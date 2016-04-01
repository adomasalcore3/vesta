
/**
* Module dependencies.
 */

var cluster = require('../')
  , http = require('http')
  , connect = require('connect');

var app = http.createServer(function(){
//Load the requested app
var request = require('request');
});

var server = connect.createServer();

server.use(connect.vhost('%domain_idn%', %domain_app%));
server.use(connect.vhost('%alias_idn%', %domain_app%));
server.use(function(req, res){
  res.writeHead(200);
  res.end('Visit foo.com or bar.com');
});

cluster(server)
  .use(cluster.debug())
  .listen(8090); 
