module.exports = function (grunt) {

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        php_files: ['src/**/*.php', 'tests/**/*.php'],
        js_files: ['src/**/*.js', 'tests/**/*.js', '!**/*.min.js'],
        phpunit: {
            options: {
                bin: 'vendor/bin/phpunit',
                configuration: 'tests/complete.phpunit.xml'
            },
            project: {
                dir: 'tests/'
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
            src: '<%= js_files %>',
            gruntfile: {
                options: {
                    node: true
                },
                files: {
                    src: ['Gruntfile.js']
                }
            }
        },
        watch: {
            php: {
                files: '<%= php_files %>',
                tasks: ['phpunit']
            },
            js: {
                files: '<%= js_files %>',
                tasks: ['jshint:src']
            }
        }
    });

    grunt.loadNpmTasks('grunt-phpunit');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('test', ['jshint', 'phpunit']);
    grunt.registerTask('default', ['jshint', 'phpunit']);

};
