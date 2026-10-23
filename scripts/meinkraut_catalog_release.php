<?php
declare(strict_types=1);
if (!defined('MK_RELEASE_INTERNAL')) {http_response_code(404);exit;}
function mk_release_check(bool $ok,string $code): void {if(!$ok)throw new RuntimeException($code);}
function mk_release_map(array $rows,string $field): array {
    $out=[];foreach($rows as $row){$id=$row[$field]??'';mk_release_check(is_string($id)&&$id!==''&&!isset($out[$id]),'duplicate_or_missing_id');$out[$id]=$row;}return $out;
}
function mk_release_key(array $a,array $e): string {
    return hash('sha256',json_encode($a,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR).json_encode($e,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR));
}
function mk_release_validate(array $q): void {
    $a=$q['atlas'];$e=$q['extra'];$ba=$q['baselineAtlas'];$be=$q['baselineExtra'];$m=$q['manifest'];
    mk_release_check($e['version']===$m['version'],'version_mismatch');
    foreach([[$a,$e,$m['expectedTarget']],[$ba,$be,$m['expectedBaseline']]] as [$at,$ex,$count]){
        mk_release_check(count($at['ingredients'])===$count['ingredients']&&count($at['recipes'])+count($ex['mixes'])===$count['recipes'],'unexpected_counts');
    }
    $ingredients=mk_release_map($a['ingredients'],'id');$countries=mk_release_map($a['countries'],'code');
    $recipes=mk_release_map(array_merge($a['recipes'],$e['mixes']),'id');
    $oldIngredients=mk_release_map($ba['ingredients'],'id');$oldRecipes=mk_release_map(array_merge($ba['recipes'],$be['mixes']),'id');
    foreach([[$ingredients,$oldIngredients,$m['addedIngredients']],[$recipes,$oldRecipes,$m['addedRecipes']]] as [$all,$old,$added]){
        foreach($old as $id=>$record)mk_release_check(isset($all[$id])&&$all[$id]===$record,'existing_record_changed');
        $actual=array_keys(array_diff_key($all,$old));sort($actual);sort($added);mk_release_check($actual===$added,'unexpected_additions');
    }
    $otherA=$a;$otherBA=$ba;unset($otherA['ingredients'],$otherA['recipes'],$otherBA['ingredients'],$otherBA['recipes']);
    mk_release_check($otherA===$otherBA,'atlas_metadata_changed');
    $otherE=$e;$otherBE=$be;unset($otherE['version'],$otherE['mixes'],$otherBE['version'],$otherBE['mixes']);
    mk_release_check($otherE===$otherBE,'extra_content_changed');
    foreach($recipes as $r){
        mk_release_check(!empty($r['ingredients']),'empty_recipe');
        foreach($r['countries'] as $country)mk_release_check(isset($countries[$country])||in_array($country,['TW','XK','PR'],true),'unknown_country');
        foreach($r['ingredients'] as $p){
            mk_release_check(isset($ingredients[$p['ingredient']]),'unknown_ingredient');
            mk_release_check(is_numeric($p['amount'])&&is_finite((float)$p['amount'])&&$p['amount']>0,'invalid_quantity');
        }
    }
    foreach($m['addedRecipes'] as $id){
        $r=$recipes[$id];mk_release_check(!empty($r['sources'])&&!empty($r['methodSteps'])&&!empty($r['safetyNotes']),'missing_recipe_evidence');
        mk_release_check(!empty($r['countries']),'missing_country');
        foreach($r['sources'] as $s)mk_release_check(str_starts_with($s['url']??'','https://')&&!empty($s['author'])&&!empty($s['contentChecked'])&&!empty($s['primarySource']),'invalid_source');
        $allergens=[];foreach($r['ingredients'] as $p){
            mk_release_check(in_array($p['unit'],['g','TL','EL','ml','Stück','Tasse'],true),'invalid_unit');
            foreach($ingredients[$p['ingredient']]['allergens']??[] as $v)$allergens[$v]=true;
            if(in_array($p['ingredient'],['senfkorner','senfsaat-schwarz','senfsaat-gelb','senfpulver'],true))$allergens['Senf']=true;
        }
        mk_release_check(!array_diff(array_keys($allergens),$r['allergens']??[]),'missing_allergen');
    }
}
function mk_catalog_release(PDO $pdo,array $q): array {
    $action=$q['action']??'';$mysql=$pdo->getAttribute(PDO::ATTR_DRIVER_NAME)==='mysql';
    mk_release_check(in_array($action,['plan','activate','rollback'],true),'unknown_action');
    if($action!=='rollback')mk_release_validate($q);
    if($action!=='plan')$pdo->beginTransaction();
    try {
        $rows=$pdo->query('SELECT * FROM mk_releases WHERE active=1'.($mysql&&$action!=='plan'?' FOR UPDATE':''))->fetchAll(PDO::FETCH_ASSOC);
        mk_release_check(count($rows)===1,'expected_one_active_release');$current=$rows[0];$previous=$current['release_key'];
        if($action==='rollback'){
            mk_release_check($previous===$q['expectedKey']&&preg_match('/^[a-f0-9]{64}$/',$q['previousKey'])===1,'release_changed');
            $st=$pdo->prepare('SELECT release_key FROM mk_releases WHERE release_key=?');$st->execute([$q['previousKey']]);mk_release_check((bool)$st->fetchColumn(),'rollback_release_missing');
            $pdo->exec('UPDATE mk_releases SET active=0 WHERE active=1');$st=$pdo->prepare('UPDATE mk_releases SET active=1 WHERE release_key=?');$st->execute([$q['previousKey']]);$pdo->commit();
            return ['status'=>'rolled_back','releaseKey'=>$q['previousKey']];
        }
        $key=mk_release_key($q['atlas'],$q['extra']);$counts=$q['manifest']['expectedTarget'];
        if($previous===$key){if($pdo->inTransaction())$pdo->commit();return ['status'=>'already_active','releaseKey'=>$key,'previousKey'=>$previous]+$counts;}
        mk_release_check(mk_release_key(json_decode($current['atlas_json'],true,512,JSON_THROW_ON_ERROR),json_decode($current['extra_json'],true,512,JSON_THROW_ON_ERROR))===mk_release_key($q['baselineAtlas'],$q['baselineExtra']),'baseline_changed');
        if($action==='plan')return ['status'=>'planned','releaseKey'=>$key,'previousKey'=>$previous]+$counts;
        mk_release_check(($q['expectedKey']??'')===$previous,'release_changed');
        $flags=JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR;
        $st=$pdo->prepare('SELECT release_key FROM mk_releases WHERE release_key=?');$st->execute([$key]);
        if(!$st->fetchColumn()){$st=$pdo->prepare('INSERT INTO mk_releases(release_key,version,active,atlas_json,extra_json) VALUES(?,?,0,?,?)');$st->execute([$key,$q['extra']['version'],json_encode($q['atlas'],$flags),json_encode($q['extra'],$flags)]);}
        $st=$pdo->prepare('DELETE FROM mk_catalog_records WHERE release_key=?');$st->execute([$key]);
        $st=$pdo->prepare('INSERT INTO mk_catalog_records(release_key,collection,record_id,payload) VALUES(?,?,?,?)');
        foreach(['ingredients'=>'id','recipes'=>'id','countries'=>'code','regions'=>'id'] as $collection=>$idField)foreach($q['atlas'][$collection] as $record)$st->execute([$key,$collection,$record[$idField],json_encode($record,$flags)]);
        foreach($q['extra']['mixes'] as $record)$st->execute([$key,'recipes',$record['id'],json_encode($record,$flags)]);
        foreach(['countries'=>'country_profiles','ingredients'=>'spice_profiles'] as $field=>$collection)foreach($q['extra'][$field] as $id=>$record)$st->execute([$key,$collection,$id,json_encode($record,$flags)]);
        $pdo->exec('UPDATE mk_releases SET active=0 WHERE active=1');$st=$pdo->prepare('UPDATE mk_releases SET active=1 WHERE release_key=?');$st->execute([$key]);
        mk_release_check((int)$pdo->query('SELECT COUNT(*) FROM mk_releases WHERE active=1')->fetchColumn()===1,'activation_failed');$pdo->commit();
        return ['status'=>'activated','releaseKey'=>$key,'previousKey'=>$previous]+$counts;
    } catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
}
