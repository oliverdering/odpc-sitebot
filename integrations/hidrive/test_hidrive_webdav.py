import contextlib
import io
import os
from pathlib import Path
import tempfile
import unittest
from unittest.mock import patch

import hidrive_webdav as h


class SafetyTests(unittest.TestCase):
    def test_path_confinement(self):
        for root in ['/','//evil/ODPC-AI-STORAGE','/x/../ODPC-AI-STORAGE',
                     '/x/%2e%2e/ODPC-AI-STORAGE','/x\\y/ODPC-AI-STORAGE',
                     '/x/ODPC-AI-STORAGE?token=secret']:
            with self.subTest(root=root), self.assertRaises(h.StorageError):
                h.safe_root(root)
        self.assertEqual(h.safe_root('/users/example/ODPC-AI-STORAGE/'),
                         '/users/example/ODPC-AI-STORAGE')

    def test_plan_uses_no_credentials_or_network(self):
        with patch.dict(os.environ, {}, clear=True), patch.object(h, 'WebDAV') as client:
            with contextlib.redirect_stdout(io.StringIO()):
                h.run('plan', [])
            client.assert_not_called()

    def test_no_writes_without_private_target_verification(self):
        with patch.dict(os.environ, {}, clear=True), patch.object(h, 'WebDAV') as client:
            with self.assertRaises(h.StorageError):
                h.run('test', [])
            client.assert_not_called()

    def test_hash_mismatch_and_conditional_put(self):
        client = object.__new__(h.WebDAV)
        with patch.object(client, 'request', side_effect=[(201,b''),(200,b'wrong')]) as req:
            with self.assertRaises(h.StorageError):
                client.put_verified('/07_BACKUPS/run/file.txt', b'original')
            self.assertEqual(req.call_args_list[0].args[3]['If-None-Match'], '*')

    def test_duplicate_names_and_symlinks_refused(self):
        with self.assertRaises(h.StorageError):
            h.run('plan', ['a/file.txt','b/file.txt'])
        with tempfile.TemporaryDirectory() as folder:
            target = Path(folder)/'target.txt'
            target.write_text('test')
            link = Path(folder)/'link.txt'
            link.symlink_to(target)
            with self.assertRaises(h.StorageError):
                h.run('plan', [str(link)])

    def test_redirect_does_not_forward_credentials(self):
        class Response:
            status = 302
        class Connection:
            requests = 0
            def __init__(self, *a, **kw): pass
            def request(self, *a, **kw): Connection.requests += 1
            def getresponse(self): return Response()
            def close(self): pass
        client = object.__new__(h.WebDAV)
        client.root = '/users/example/ODPC-AI-STORAGE'
        client.authorization = 'Basic test-only'
        with patch.object(h.http.client, 'HTTPSConnection', Connection):
            with self.assertRaises(h.StorageError):
                client.request('GET', '/file.txt')
        self.assertEqual(Connection.requests, 1)

    def test_partial_run_has_no_complete_marker(self):
        with patch.dict(os.environ, {'HIDRIVE_PRIVATE_TARGET_VERIFIED':'true'}):
            with patch.object(h, 'WebDAV') as cls:
                client = cls.return_value
                client.create_run.return_value = '/07_BACKUPS/test-run'
                client.put_verified.side_effect = h.StorageError('mismatch')
                with self.assertRaises(h.StorageError):
                    h.run('test', [])
                self.assertEqual(client.put_verified.call_count, 1)
                self.assertNotIn('COMPLETE.json', client.put_verified.call_args.args[0])


if __name__ == '__main__':
    unittest.main()
