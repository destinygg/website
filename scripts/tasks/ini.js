const fs = require('fs')
const pkg = require('../../package.json')

function replaceAppVersion(file){
    let data = fs.readFileSync(file, 'utf8');
    const result = data.replace(
        /^define(?:\s+)?\((?:\s+)?['"]_APP_VERSION['"].*$/gm,
        'define(\'_APP_VERSION\', \''+pkg.version+'\'); // auto-generated: ' + new Date().getTime()
    );
    fs.writeFileSync(file, result, 'utf8');
    console.log('Update _APP_VERSION ['+pkg.version+']['+ file +']')
}

replaceAppVersion('lib/boot.app.php')
replaceAppVersion('lib/boot.test.php')