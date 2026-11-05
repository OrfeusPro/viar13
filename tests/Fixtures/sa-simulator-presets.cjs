// Execute the actual preset builder against isolated fixture defaults.
const fs = require('fs');
const vm = require('vm');
const path = require('path');
const source = fs.readFileSync(path.join(__dirname, '../../resources/views/filament/pages/sa-simulator.blade.php'), 'utf8');
const uuid = source.slice(source.indexOf('    function genUuidV4()'), source.indexOf('    function resolveMethodByEndpoint'));
const builder = source.slice(source.indexOf('    function buildPresets()'), source.indexOf('    function normalizeValidationDetails'))
  .replace(/\{\{ rtrim\(config\('app.url'\), '\/'\) \}\}/g, 'https://fixture.invalid');
const context = { window: { saSimulatorDefaults: JSON.parse(fs.readFileSync(process.argv[2], 'utf8')) } };
vm.createContext(context);
vm.runInContext(uuid + builder + '\nthis.presets = buildPresets();', context);
process.stdout.write(JSON.stringify(context.presets));
