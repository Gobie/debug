var JSHINT_DEFAULT = {
    reporter: require('jshint-stylish'),
    curly: true,    // true: Require {} for every new block or scope
    eqeqeq: true,   // true: Require triple equals (===) for comparison
    immed: true,    // true: Require immediate invocations to be wrapped in parenthesis e.g. `(function () { } ());`
    latedef: true,  // true: Require variables/functions to be defined before being used
    newcap: true,   // true: Require capitalization of all constructor functions e.g. `new F()`
    noarg: true,    // true: Prohibit use of `arguments.caller` and `arguments.callee`
    sub: true,      // true: Tolerate using `[]` notation when it can still be expressed in dot notation
    undef: true,    // true: Require all non-global variables to be declared (prevents global leaks)
    boss: true,     // true: Tolerate assignments where comparisons would be expected
    eqnull: true    // true: Tolerate use of `== null`
}, JSHINT_BROWSER = {
    browser: true,  // true: Defines globals exposed by modern browsers
    node: false     // false: Doesn't define globals exposed by node.js
}, JSHINT_NODE = {
    browser: false, // false: Doesn't define globals exposed by modern browsers
    node: true      // true: Defines globals exposed by node.js
};

module.exports = function (grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        files: {
            server: {
                php: ['src/**/*.php', 'tests/**/*.php'],
                js_tests: ['tests/**/*.js'],
                js_support: ['*.js']
            },
            client: {
                js: ['src/**/*.js', '!**/*.min.js', '!**/debugger.js']
            }
        },

        phpunit: {
            options: {
                bin: 'vendor/bin/phpunit',
                configuration: 'tests/complete.phpunit.xml'
            },
            unit: {
                dir: 'tests/'
            }
        },

        karma: {
            options: {
                configFile: 'karma.conf.js',
                reporters: ['dots']
            },
            unit: {
                background: true
            },
            continuous: {
                browsers: ['PhantomJS'],
                singleRun: true
            }
        },

        jshint: {
            options: JSHINT_DEFAULT,
            client: {
                options: JSHINT_BROWSER,
                files: ['<%= files.client.js %>']
            },
            server_tests: {
                options: JSHINT_NODE,
                files: ['<%= files.server.js_tests %>']
            },
            server_support: {
                options: JSHINT_NODE,
                files: ['<%= files.server.js_support %>']
            }
        },

        watch: {
            php_tests: {
                files: ['<%= files.server.php %>'],
                tasks: ['phpunit']
            },
            karma_tests: {
                files: ['<%= files.server.js_tests %>'],
                tasks: ['karma:unit:run']
            },
            jshint_client: {
                files: ['<%= files.client.js %>'],
                tasks: ['jshint:client']
            },
            jshint_server_tests: {
                files: ['<%= files.server.js_tests %>'],
                tasks: ['jshint:server_tests']
            },
            jshint_server_support: {
                files: ['<%= files.server.js_support %>'],
                tasks: ['jshint:server_support']
            }
        }
    });

    require('load-grunt-tasks')(grunt);

    grunt.registerTask('test', ['phpunit', 'jshint', 'karma']);
    grunt.registerTask('default', ['test']);

};
