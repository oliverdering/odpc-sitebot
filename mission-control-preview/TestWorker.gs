/** Synthetic-only pilot. Install as a spreadsheet-bound Apps Script; no trigger is created automatically. */
function missionControlSyntheticOnce() {
  const lock = LockService.getDocumentLock();
  if (!lock || !lock.tryLock(5000)) throw new Error('Test lock unavailable');
  try {
    const sheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName('MissionControl_Test');
    if (!sheet) throw new Error('Test sheet missing');
    const row = sheet.getRange(2, 1, 1, 6).getValues()[0];
    if (row[0] !== 'MC-SYNTH-20261114-001' || row[1] !== 'NEU' || row[2] !== 'synthetic ODPC ping' || row[3] || row[4] || row[5]) return 'SKIPPED';
    const run = Utilities.getUuid();
    sheet.getRange(2, 1, 1, 6).setValues([[row[0], 'ERLEDIGT', row[2], run, 'TEST_OK: synthetischer Ping', new Date().toISOString() + ' Apps Script synthetic worker']]);
    SpreadsheetApp.flush();
    return run;
  } finally { lock.releaseLock(); }
}
