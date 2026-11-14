import os,tempfile,threading,unittest,sqlite3
import worker
class Tests(unittest.TestCase):
 def test_concurrency(self):
  with tempfile.TemporaryDirectory() as d:
   p=os.path.join(d,'t.db'); c=worker.db(p);worker.enqueue(c,'T-001','synthetic');c.close(); out=[]
   def go():
    c=worker.db(p);out.append(worker.run(c));c.close()
   threads=[threading.Thread(target=go) for _ in range(4)]
   for t in threads:t.start()
   for t in threads:t.join()
   self.assertEqual(sum(x is not None for x in out),1)
   c=worker.db(p);self.assertEqual(c.execute('select status from tasks').fetchone()[0],'DONE');self.assertEqual([r[0] for r in c.execute('select event from audit')],['QUEUED','CLAIMED','DONE'])
   with self.assertRaises(sqlite3.IntegrityError):worker.enqueue(c,'T-001','again')
   c.close()
if __name__=='__main__':unittest.main()
