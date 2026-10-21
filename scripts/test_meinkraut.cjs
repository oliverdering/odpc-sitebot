const assert=require('node:assert/strict'),fs=require('node:fs'),vm=require('node:vm'),path=require('node:path');
const dir=path.join(__dirname,'../site-deploy/meinkraut');
const db=JSON.parse(fs.readFileSync(path.join(dir,'content/atlas-base.json'))),extra=JSON.parse(fs.readFileSync(path.join(dir,'content/meinkraut-content.json')));
const ingredientIds=new Set(db.ingredients.map(i=>i.id)),recipeIds=new Set();
for(const r of [...db.recipes,...extra.mixes]){assert(!recipeIds.has(r.id),'duplicate recipe');recipeIds.add(r.id);assert(r.ingredients.length);for(const p of r.ingredients){assert(ingredientIds.has(p.ingredient),r.id+' missing '+p.ingredient);assert(p.amount>0);}}
for(const r of extra.mixes){assert(Math.abs(r.ingredients.reduce((a,p)=>a+p.amount,0)-100)<1e-8);assert(r.ingredients.every(p=>p.unit==='g'));assert(r.methodSteps.length>=3);}
for(const c of Object.values(extra.countries)){assert(c.intro.length>300);assert(c.typicalDishes.length>=4);assert(c.typicalSpices.every(id=>ingredientIds.has(id)));assert(c.sources.every(x=>/^https:\/\//.test(x.url)));}
for(const [id,k] of Object.entries(extra.ingredients)){assert(ingredientIds.has(id));assert(k.origin&&k.cultivation&&k.harvestProcessing&&k.storage&&k.sources.length);}
const root={innerHTML:'',append(){},addEventListener(){},querySelector(){return null;}};
const catalog=JSON.parse(fs.readFileSync(path.join(dir,'content/shop-catalog.json')));
const window={WGA_DATA:db,MEINKRAUT_CONTENT:extra,MEINKRAUT_DEBUG:true,MEINKRAUT_SHOP:catalog,MEINKRAUT_SHOP_DEBUG:true};
const sandbox={window,document:{getElementById:id=>id==='wga-root'?root:null,createElement(){return {setAttribute(){},remove(){}};},addEventListener(){},body:{style:{}}},localStorage:{getItem(){return null;},setItem(){}},location:{hash:''},history:{},console,structuredClone,setTimeout:()=>0,clearTimeout,Map,Set,Date,Number,String,Array,Object,Math,JSON,crypto:require('node:crypto').webcrypto};
vm.createContext(sandbox);vm.runInContext(fs.readFileSync(path.join(dir,'shop.js'),'utf8'),sandbox);
vm.runInContext(fs.readFileSync(path.join(dir,'meinkraut.js'),'utf8'),sandbox);
const api=window.MEINKRAUT_TEST,state=api.getState();
for(const r of extra.mixes)assert(api.canMix(r),r.id+' must be weighable');
state.parts=[{id:'salz',grams:35,enabled:true},{id:'schwarzer-pfeffer',grams:65,enabled:true}];state.target=200;
assert.equal(api.currentRows().reduce((a,p)=>a+p.amount,0),200);assert.equal(api.currentRows()[0].amount,70);
state.parts[0].enabled=false;assert.equal(api.currentRows()[1].amount,200);assert.equal(api.currentRows()[0].amount,0);
state.parts[1].enabled=false;assert(api.currentRows().every(p=>p.amount===0&&p.percent===0));
assert(!api.validateSaved({id:'bad',name:'bad',parts:[{id:'unknown',grams:10}],target:100}));
assert(api.validateSaved({id:'valid',name:'valid',parts:[{id:'salz',grams:10}],target:100}));
for(const page of ['start','rezepte','regal','laender','regionen','werkstatt','wissen','dosen','kosten','meine','einkauf','shop','premium','refill','warenkorb','etiketten','konto']){state.page=page;api.render();assert(root.innerHTML.includes('mk-main'),page+' render');if(page==='kosten'){assert(root.innerHTML.includes('5,49'));assert(root.innerHTML.includes('0,27'));assert(root.innerHTML.includes('38,95'));}}
state.page='start';api.render();
assert(root.innerHTML.includes('Mein Kraut')&&root.innerHTML.includes('649'));
const commerce=window.MEINKRAUT_SHOP_TEST;const cart=commerce.getState();
assert.equal(catalog.checkoutEnabled,false);assert.equal(catalog.products.filter(p=>p.line==='basic').length,10);assert.equal(catalog.products.filter(p=>p.line==='premium').length,9);
assert.equal(commerce.suggestPacks(480,[250,500],250)[0].grams,500);assert.equal(commerce.suggestPacks(0,[250,500],250).length,0);
assert(commerce.add('pfeffer-schwarz-250',1));assert(!commerce.add('invalid-sku',1));assert(!commerce.validateState({cart:[{id:'invalid',qty:1}],shelf:{},history:[]}));
cart.shelf['schwarzer-pfeffer']={grams:25,preferred:250};let plan=commerce.refillRows([{id:'schwarzer-pfeffer',amount:100}],new Set());assert.equal(plan[0].need,75);assert.equal(plan[0].packs[0].grams,250);
cart.shelf['schwarzer-pfeffer'].grams=null;plan=commerce.refillRows([{id:'schwarzer-pfeffer',amount:100}],new Set(['schwarzer-pfeffer']));assert(plan[0].ownedUnknown);assert.equal(plan[0].available,null);
const label=commerce.getLabel();label.name='<img src=x onerror=alert(1)>';assert(!commerce.labelFace().includes('<img src=x'));assert(commerce.labelFace().includes('&lt;img'));assert(commerce.validateLabel({...label}));assert(!commerce.validateLabel({...label,style:'invalid'}));
assert(window.MEINKRAUT_COMMERCE.validateBackup(window.MEINKRAUT_COMMERCE.backup()));assert(!window.MEINKRAUT_COMMERCE.validateBackup({shop:{},labels:[]}));
console.log(JSON.stringify({status:'PASS',ingredients:db.ingredients.length,recipes:recipeIds.size,starterMixes:extra.mixes.length,countryProfiles:Object.keys(extra.countries).length,spiceProfiles:Object.keys(extra.ingredients).length,checks:['IDs and references','100 g recipe sums','country structure','spice structure','mass scaling','exclusion rebalancing','empty mixture','import validation','all page rendering including sourced cost comparison','catalog and payment gate','pack optimisation','known and unknown stock handling','invalid SKU rejection','label escaping and validation']}));
