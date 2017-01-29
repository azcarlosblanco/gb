var connect = require('connect');
var serveStatic = require('serve-static');
connect().use(serveStatic(__dirname)).listen(process.argv[2] || 80, function(){
    if (process.argv[2]) {
      console.log('Server running on ' + process.argv[2] );
    } else {
      console.log('Server running on 80');
    }
});
