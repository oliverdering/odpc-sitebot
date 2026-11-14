import sqlite3,uuid,json,datetime,sys

def ts(): return datetime.datetime.now(datetime.timezone.utc).isoformat()
def db(path):
 c=sqlite3.connect(path,timeout=10,isolation_level=None)
 c.executescript('CREATE TABLE IF NOT EXISTS tasks(id TEXT PRIMARY KEY,status TEXT,payload TEXT,run_id TEXT,result TEXT); CREATE TABLE IF NOT EXISTS audit(task_id TEXT,run_id TEXT,event TEXT,at TEXT);')
 return c
def enqueue(c,id,payload):
 c.execute('BEGIN IMMEDIATE')
 try:
  c.execute('INSERT INTO tasks VALUES(?,?,?,NULL,NULL)',(id,'QUEUED',payload));c.execute('INSERT INTO audit VALUES(?,?,?,?)',(id,None,'QUEUED',ts()));c.execute('COMMIT')
 except Exception: c.execute('ROLLBACK');raise
def run(c):
 c.execute('BEGIN IMMEDIATE')
 try:
  row=c.execute("SELECT id,payload FROM tasks WHERE status='QUEUED' ORDER BY rowid LIMIT 1").fetchone()
  if not row: c.execute('COMMIT');return None
  id,payload=row;rid=str(uuid.uuid4());c.execute("UPDATE tasks SET status='RUNNING',run_id=? WHERE id=?",(rid,id));c.execute('INSERT INTO audit VALUES(?,?,?,?)',(id,rid,'CLAIMED',ts()));c.execute('COMMIT')
 except Exception:c.execute('ROLLBACK');raise
 result=json.dumps({'worker':'local-test','input_length':len(payload)})
 c.execute('BEGIN IMMEDIATE')
 try:
  n=c.execute("UPDATE tasks SET status='DONE',result=? WHERE id=? AND status='RUNNING' AND run_id=?",(result,id,rid)).rowcount
  if n!=1:raise RuntimeError('claim lost')
  c.execute('INSERT INTO audit VALUES(?,?,?,?)',(id,rid,'DONE',ts()));c.execute('COMMIT')
 except Exception:c.execute('ROLLBACK');raise
 return id,rid
if __name__=='__main__':
 c=db(sys.argv[1]);op=sys.argv[2]
 if op=='enqueue':enqueue(c,sys.argv[3],sys.argv[4])
 elif op=='run':print(run(c))
 elif op=='show':print(json.dumps({'tasks':c.execute('select * from tasks').fetchall(),'audit':c.execute('select * from audit').fetchall()},indent=2))
 c.close()
