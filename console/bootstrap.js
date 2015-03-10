(function () {
  'use strict';

  var fs = require('fs'),
      vm = require('vm'),
      util = require('util'),
      repl = require('repl'),
      jsdom = require('jsdom'),
      js2php = require('js2php'),
      html = '<html><body></body></html>',
      Table = require('cli-table'),
      XMLHttpRequest = require('xmlhttprequest'),
      FormData = require('form-data'),
      WebSocket = require('ws'),
      argv = require('minimist')(process.argv.slice(2)),
      isServer = (argv.server == true),
      evaluateFile = argv._[1];

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

      try {
        script = vm.createScript(commandBuffer + cmd);

        if (isServer) {
          // server-side REPL
          var code = js2php(commandBuffer + cmd ), lines = code.split("\n");
          // remove '<?php' from first line
          lines.shift();
          // add return statement to the last line
          lines[lines.length - 2] = "return " + lines[lines.length -2];

          code = lines.join("\n");
          result = hook.post('apps/evaluate', { code: code });
        } else {
          // client REPL
          result = script.runInNewContext(context);
        }

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

        config = JSON.parse(fs.readFileSync(argv._[0]));

    // Create browser client
    var hook = new window.Hook.Client(config);

    if (!evaluateFile) {
      var prompt = (isServer) ? 'server' : 'client',
          availableVariables = ['config - .hook-config'];

      if (!isServer) {
        console.log("\rClient-side playground.");
        console.log("API Documentation: http://doubleleft.github.io/hook-javascript\n");

        availableVariables.push("hook - Hook.Client");
        availableVariables.push("$ - jQuery 2.1.0");
        availableVariables.push("window");
      } else {
        console.log("\rServer-side playground.");
      }

      console.log("\rAvailable variables to hack on:");
      for (var i = 0; i < availableVariables.length; i++) {
        console.log("\t- " + availableVariables[i]);
      }
      console.log();

      sess = repl.start({
        prompt: (prompt + ': javascript> '),
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
