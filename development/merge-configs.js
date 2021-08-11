const fs                        = require('fs');
const defaultConfig             = require('./config.default.json');
let localConfig                 = {};

if ( fs.existsSync('./development/config.local.json') ) {
	localConfig = require('./config.local.json');
}

const config = { ...defaultConfig.config, ...localConfig };

module.exports = config;
