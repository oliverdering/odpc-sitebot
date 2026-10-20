"""Full private server snapshot. No database/configuration leaves the hosting account."""
import sys, os, json, shlex, hashlib, base64, hmac, posixpath
from pathlib import Path
sys.path.insert(0,str(Path(__file__).resolve().parents[1]))
import paramiko
from deploy_helpermonkey_v4 import find_theme, HOST, USER, PIN

def command(t, cmd, payload=None):
 c=t.open_session(timeout=25); c.settimeout(600); c.exec_command(cmd)
 if payload: c.sendall(payload.encode())
 c.shutdown_write(); data=c.makefile('rb').read(); err=c.makefile_stderr('rb').read(); code=c.recv_exit_status()
 if code: raise RuntimeError('Remote backup operation failed; exit '+str(code))
 return data.decode()

def main():
 t=paramiko.Transport((HOST,22));t.start_client(timeout=25)
 try:
  fp='SHA256:'+base64.b64encode(hashlib.sha256(t.get_remote_server_key().asbytes()).digest()).decode().rstrip('=')
  if not hmac.compare_digest(fp,PIN): raise RuntimeError('Host key mismatch')
  t.auth_password(USER,os.environ['ODPC_SFTP_PASSWORD']);s=paramiko.SFTPClient.from_transport(t)
  root=find_theme(s).split('/wp-content/themes/')[0]
  run=os.environ.get('GITHUB_RUN_ID','manual'); dest=posixpath.dirname(root)+'/.helpermonkey-private-backups/'+run
  # A shell is not assumed. Verify command execution before touching production.
  print('SSH_PHP',command(t,'php -r '+shlex.quote('echo PHP_VERSION;')).strip())
  php=r'''<?php
ini_set('display_errors','0'); error_reporting(0);
$root=ROOT_VALUE; $dest=DEST_VALUE;
if(!is_file($root.'/wp-config.php')) {fwrite(STDERR,"root mismatch");exit(2);}
if(!is_dir($dest)&&!mkdir($dest,0700,true))exit(3);chmod(dirname($dest),0700);chmod($dest,0700);
define('WP_USE_THEMES',false);require $root.'/wp-load.php';
global $wpdb;
$tables=$wpdb->get_col('SHOW TABLES');if(!$tables)exit(4);
$f=fopen($dest.'/database.sql','wb');if(!$f)exit(5);chmod($dest.'/database.sql',0600);
fwrite($f,"SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n");
$wpdb->query('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');$wpdb->query('START TRANSACTION WITH CONSISTENT SNAPSHOT');
$counts=[];
foreach($tables as $table){
 $ident='`'.str_replace('`','``',$table).'`';$ddl=$wpdb->get_row('SHOW CREATE TABLE '.$ident,ARRAY_N);if(!$ddl)exit(6);
 fwrite($f,"DROP TABLE IF EXISTS $ident;\n".$ddl[1].";\n");$offset=0;
 do{$rows=$wpdb->get_results('SELECT * FROM '.$ident.' LIMIT '.$offset.',250',ARRAY_A);if($wpdb->last_error)exit(7);
 foreach($rows as $row){$values=[];foreach($row as $v)$values[]=$v===null?'NULL':"X'".bin2hex($v)."'";
 fwrite($f,'INSERT INTO '.$ident.' VALUES ('.implode(',',$values).");\n");}$offset+=count($rows);
 }while(count($rows)===250);$counts[$table]=$offset;
}
$wpdb->query('COMMIT');fwrite($f,"SET FOREIGN_KEY_CHECKS=1;\n");fclose($f);
$m=['site'=>home_url(),'theme'=>get_stylesheet(),'table_count'=>count($tables),'row_count'=>array_sum($counts),'database_sha256'=>hash_file('sha256',$dest.'/database.sql'),'root'=>$root,'snapshot'=>$dest];
file_put_contents($dest.'/manifest.json',json_encode($m,JSON_PRETTY_PRINT));chmod($dest.'/manifest.json',0600);
echo json_encode($m);
'''.replace('ROOT_VALUE',json.dumps(root)).replace('DEST_VALUE',json.dumps(dest))
  info=json.loads(command(t,'php',php));
  if info['site'].rstrip('/')!='https://ai.odpc.de':raise RuntimeError('Wrong site in snapshot')
  command(t,'tar -czf '+shlex.quote(dest+'/files.tar.gz')+' -C '+shlex.quote(root)+' .')
  command(t,'chmod 600 '+shlex.quote(dest+'/files.tar.gz'))
  command(t,'tar -tzf '+shlex.quote(dest+'/files.tar.gz'))
  with s.open(dest+'/database.sql','rb') as f:
   first=f.read(64)
  if not first.startswith(b'SET NAMES'):raise RuntimeError('Database header check failed')
  info['archive_bytes']=s.stat(dest+'/files.tar.gz').st_size
  info['database_bytes']=s.stat(dest+'/database.sql').st_size
  if info['archive_bytes']<1000 or info['database_bytes']<1000:raise RuntimeError('Snapshot unexpectedly small')
  # Only non-secret manifest is published; full dump and wp-config remain private on server.
  Path('backup-manifest.json').write_text(json.dumps(info,indent=2))
  print('FULL_BACKUP_VERIFIED',json.dumps(info))
 finally:t.close()
if __name__=='__main__':
 try:main()
 except Exception as e:print('BACKUP_BLOCKED',str(e));sys.exit(1)
