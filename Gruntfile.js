module.exports = function (grunt) {

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        files: {
            server: {
                php: ['src/**/*.php', 'tests/**/*.php'],
                js_tests: ['tests/**/*.js'],
                js_config: ['*.js']
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
            options: {
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
            },
            client: '<%= files.client.js %>',
            server: {
                options: {
                    node: true
                },
                files: ['<%= files.server.js_config %>', '<%= files.server.js_tests %>']
            }
        },
        watch: {
            php_tests: {
                files: '<%= files.server.php %>',
                tasks: ['phpunit']
            },
            js_tests: {
                files: '<%= files.server.js_tests %>',
                tasks: ['karma:unit:run']
            },
            js_lint: {
                files: ['<%= files.server.js_config %>', '<%= files.server.js_tests %>', '<%= files.client.js %>'],
                tasks: ['jshint']
            }
        }
    });

    require('load-grunt-tasks')(grunt);

    grunt.registerTask('test', ['phpunit', 'jshint', 'karma']);
    grunt.registerTask('default', ['test']);

};
