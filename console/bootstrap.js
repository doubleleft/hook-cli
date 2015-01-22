(function () {
  'use strict';

  var fs = require('fs'),
      vm = require('vm'),
      util = require('util'),
      repl = require('repl'),
      jsdom = require('jsdom'),
      html = '<html><body></body></html>',
      Table = require('cli-table'),
      XMLHttpRequest = require('xmlhttprequest'),
      FormData = require('form-data'),
      WebSocket = require('ws'),
      evaluateFile = (process.argv[3]);

  if (!evaluateFile) {
    console.log(" _                 _    ");
    console.log("| |__   ___   ___ | | __");
    console.log("| '_ \\ / _ \\ / _ \\| |/ /");
    console.log("| | | | (_) | (_) |   < ");
    console.log("|_| |_|\\___/ \\___/|_|\\_\\");
    console.log("");
  }

  process.stdout.write("Loading...");

  // first argument can be html string, filename, or url
  jsdom.env(html, ["./node_modules/hook-javascript/dist/hook.js"], function (errors, window) {

    // Define browser features
    // -----------------------
    // dummy localstorage
    window.localStorage = {
      _items: {},
      getItem: function(name) { return this._items[name]; },
      setItem: function(name, value) { this._items[name] = value; }
    };
    window.FormData = FormData;
    window.WebSocket = WebSocket;
    window.Blob = function Blob() {};
    window.Blob.constructor = Buffer.prototype;

    var promises = [];

    var commandBuffer = "";
    function evaluate(cmd, context, filename, callback) {
      var result, script;
      cmd = cmd.substr(1, cmd.length-2)

      try {
        script = vm.createScript(commandBuffer + cmd);
        result = script.runInNewContext(context);

        // clear buffer for next command
        commandBuffer = "";
      } catch (e) {

        // accumulate command in buffer if it's not finished.
        if (e.message == "Unexpected end of input") {
          commandBuffer += cmd;
          return;
        }
      }

      if(result && result.constructor && result.constructor.name == 'Promise'){
        result.then(function(data) {
          callback(null, [data, 'table']);
        }).otherwise(function(data) {
          callback(null, [data, 'inspect']);
        });
      } else {
        callback(null, [result, 'inspect']);
      }
    }

    function writer(result) {
      if (result[1] == 'table') {
        return prettyPrint(result[0]);
      } else {
        return util.inspect(result[0], {colors: true});
      }
    }

    function prettyPrint(data, pointer){
      var options = {};

      // Print table for arrays
      if (typeof(data)==="object" && data.length && data.length > 0) {

        if (!options.timestamps) {
          delete data[0].created_at;
          delete data[0].updated_at;
        }

        var keys = Object.keys(data[0]),
            table = new Table({ head: keys });

        for (var i=0; i < data.length; i++) {

          if (!options.timestamps) {
            delete data[i].created_at;
            delete data[i].updated_at;
          }

          var values = [];
          for (var k in data[i]) {
            values.push(util.inspect(data[i][k], {colors: true}));
          }
          table.push(values);
        }

        return table.toString();
      } else {
        // Pretty general output
        return util.inspect(data, {colors: true});
      }
      // pointer.displayPrompt();
    }

    var sess,
        $ = require('jquery')(window),
        config = JSON.parse(fs.readFileSync(process.argv[2]));

    // Create browser client
    var hook = new window.Hook.Client(config);

    if (!evaluateFile) {
      console.log("\rAPI Documentation: http://doubleleft.github.io/hook-javascript\n");
      console.log("Available variables to hack on:");
      console.log("\t- hook - Hook.Client");
      console.log("\t- config - .hook-config");
      console.log("\t- $ - jQuery 2.1.0");
      console.log("\t- window");

      sess = repl.start({
        prompt: 'hook: javascript> ',
        eval: evaluate,
        writer: writer,
        ignoreUndefined: true
      });
    } else {
      process.stdout.write("\r             \r");
      eval(fs.readFileSync(evaluateFile, "utf-8"));
    }

    //
    // Custom inspecting
    //
    // window.Hook.Collection.prototype.inspect = function() {
    //   return "[Collection: '" + this.name + "']";
    // };

    if (sess) {
      sess.context.window = window;
      sess.context.$ = window.$;
      sess.context.Hook = window.Hook;
      sess.context.config = config;
      sess.context.hook = hook;
      sess.context.sess = sess;
    }

  });
}());
