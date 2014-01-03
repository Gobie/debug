module.exports = function (grunt) {

    require('time-grunt')(grunt);

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
            options: {
                jshintrc: '.jshintrc',
                reporter: require('jshint-stylish')
            },
            client: {
                options: {
                    browser: true
                },
                files: ['<%= files.client.js %>']
            },
            server_tests: {
                options: {
                    node: true
                },
                files: ['<%= files.server.js_tests %>']
            },
            server_support: {
                options: {
                    node: true
                },
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
