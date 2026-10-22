<?php
declare(strict_types=1);
define('MK_RELEASE_INTERNAL',true);require __DIR__.'/meinkraut_catalog_release.php';
$repo=dirname(__DIR__);$m=json_decode(file_get_contents($repo.'/deployment/meinkraut-catalog-release.json'),true,512,JSON_THROW_ON_ERROR);
$q=['manifest'=>$m,'action'=>'plan'];
foreach(['Atlas'=>'atlas-base.json','Extra'=>'meinkraut-content.json'] as $key=>$file){
    $path='site-deploy/meinkraut/content/'.$file;
    $q[strtolower($key)]=json_decode(file_get_contents($repo.'/'.$path),true,512,JSON_THROW_ON_ERROR);
    $q['baseline'.$key]=json_decode(shell_exec('git -C '.escapeshellarg($repo).' show '.escapeshellarg($m['baselineRevision'].':'.$path)),true,512,JSON_THROW_ON_ERROR);
}
$pdo=new PDO('sqlite::memory:',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$pdo->exec('CREATE TABLE mk_releases(release_key TEXT PRIMARY KEY, version TEXT, active INTEGER, atlas_json TEXT, extra_json TEXT)');
$pdo->exec('CREATE TABLE mk_catalog_records(release_key TEXT, collection TEXT, record_id TEXT, payload TEXT, PRIMARY KEY(release_key,collection,record_id))');
$pdo->exec("CREATE TABLE mk_shop_state(id INTEGER PRIMARY KEY,payload TEXT); INSERT INTO mk_shop_state VALUES(1,'preserve user shelf')");
$key=mk_release_key($q['baselineAtlas'],$q['baselineExtra']);$st=$pdo->prepare('INSERT INTO mk_releases VALUES(?,?,1,?,?)');$st->execute([$key,$q['baselineExtra']['version'],json_encode($q['baselineAtlas']),json_encode($q['baselineExtra'])]);
$plan=mk_catalog_release($pdo,$q);mk_release_check($plan['status']==='planned','plan_failed');
mk_release_check((int)$pdo->query('SELECT COUNT(*) FROM mk_releases')->fetchColumn()===1,'plan_mutated');
foreach(['changed_baseline','changed_existing','unknown_reference','missing_allergen','wrong_expected_key'] as $probe){
    $bad=$q;$bad['action']='activate';$bad['expectedKey']=$key;
    if($probe==='changed_baseline')$bad['baselineExtra']['version']='wrong';
    if($probe==='changed_existing')$bad['atlas']['ingredients'][0]['name']='changed';
    if($probe==='unknown_reference')$bad['atlas']['recipes'][count($bad['atlas']['recipes'])-1]['ingredients'][0]['ingredient']='unknown';
    if($probe==='missing_allergen')$bad['atlas']['recipes'][count($bad['atlas']['recipes'])-1]['allergens']=[];
    if($probe==='wrong_expected_key')$bad['expectedKey']=str_repeat('0',64);
    $rejected=false;try{mk_catalog_release($pdo,$bad);}catch(Throwable $e){$rejected=true;}mk_release_check($rejected,$probe.'_accepted');
    mk_release_check($pdo->query('SELECT release_key FROM mk_releases WHERE active=1')->fetchColumn()===$key,'failed_request_changed_release');
}
$q['action']='activate';$q['expectedKey']=$key;$result=mk_catalog_release($pdo,$q);mk_release_check($result['status']==='activated','activate_failed');
mk_release_check(mk_catalog_release($pdo,$q)['status']==='already_active','idempotence_failed');
mk_release_check((int)$pdo->query('SELECT COUNT(*) FROM mk_releases')->fetchColumn()===2,'history_missing');
mk_release_check($pdo->query('SELECT payload FROM mk_shop_state WHERE id=1')->fetchColumn()==='preserve user shelf','user_state_changed');
$st=$pdo->prepare("SELECT COUNT(*) FROM mk_catalog_records WHERE release_key=? AND collection='recipes'");$st->execute([$result['releaseKey']]);mk_release_check((int)$st->fetchColumn()===$m['expectedTarget']['recipes'],'record_count');
$rollback=mk_catalog_release($pdo,['action'=>'rollback','expectedKey'=>$result['releaseKey'],'previousKey'=>$key]);mk_release_check($rollback['status']==='rolled_back','rollback_failed');
mk_release_check($pdo->query('SELECT release_key FROM mk_releases WHERE active=1')->fetchColumn()===$key,'rollback_active_failed');
echo json_encode(['status'=>'PASS','checks'=>['read-only plan','append-only validation','baseline lock','unknown reference denied','missing allergen denied','release lease','transactional activation','idempotence','release history','user shelf preserved','recipe index count','rollback']])."\n";
