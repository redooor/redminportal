/*!
 * RedminPortal's Gruntfile
 * https://github.com/redooor/redminportal
 * Copyright 2013-2015 Redooor LLP
 * Licensed under MIT
 */

module.exports = function (grunt) {
    'use strict';

    grunt.loadNpmTasks('grunt-autoprefixer');
    grunt.loadNpmTasks('grunt-banner');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-connect');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');

    var configBridge = grunt.file.readJSON('./bower_components/bootstrap/grunt/configBridge.json', {
        encoding: 'utf8'
    });

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        builddir: '.',
        buildtheme: '',
        banner: '/*!\n' +
            ' * <%= pkg.name %> v<%= pkg.version %>\n' +
            ' * Homepage: <%= pkg.homepage %>\n' +
            ' * Copyright 2014-<%= grunt.template.today("yyyy") %> <%= pkg.author %>\n' +
            ' * Licensed under <%= pkg.license %>\n' +
            '*/\n',
        less: {
            compileCore: {
                options: {
                    strictMath: true,
                    sourceMap: true,
                    outputSourceFiles: true,
                    sourceMapURL: '<%= pkg.name %>.css.map',
                    sourceMapFilename: 'public/assets/css/<%= pkg.name %>.css.map'
                },
                src: 'src/less/redminportal.less',
                dest: 'public/assets/css/<%= pkg.name %>.css'
            }
        },
        autoprefixer: {
            options: {
                browsers: configBridge.config.autoprefixerBrowsers
            },
            core: {
                options: {
                    map: true
                },
                src: 'public/assets/css/<%= pkg.name %>.css'
            }
        },
        cssmin: {
            options: {
                compatibility: 'ie8',
                keepSpecialComments: '*',
                noAdvanced: true
            },
            minifyCore: {
                src: 'public/assets/css/<%= pkg.name %>.css',
                dest: 'public/assets/css/<%= pkg.name %>.min.css'
            }
        },
        usebanner: {
            options: {
                position: 'top',
                banner: '<%= banner %>'
            },
            files: {
                src: 'public/assets/css/<%= pkg.name %>.css'
            }
        },
        copy: {
            fonts: {
                expand: true,
                cwd: 'bower_components/bootstrap/fonts/',
                src: '**',
                dest: 'public/assets/fonts/',
                flatten: true,
                filter: 'isFile'
            },
            jquery: {
                expand: true,
                cwd: 'bower_components/jquery/dist/',
                src: '**',
                dest: 'public/assets/js/jquery/',
                flatten: true,
                filter: 'isFile'
            },
            bootstrapjs: {
                src: 'bower_components/bootstrap/dist/js/bootstrap.min.js',
                dest: 'public/assets/js/bootstrap.min.js'
            },
            jqueryui: {
                expand: true,
                cwd: 'bower_components/jquery-ui/themes/blitzer/',
                src: '**',
                dest: 'public/assets/css/jquery-ui/themes/blitzer/'
            },
            jqueryuijs: {
                src: 'bower_components/jquery-ui/jquery-ui.min.js',
                dest: 'public/assets/js/jquery-ui/jquery-ui.min.js'
            }
        },
        watch: {
            files: ['src/less/**/*.less'],
            tasks: 'default',
            options: {
                livereload: true,
                nospawn: true
            }
        },
        connect: {
            server: {
                options: {
                    port: 3000,
                    base: '.'
                }
            }
        }
    });

    grunt.registerTask('none', function () {});
    
    // Copy Bootstrap less, compile and minify
    grunt.registerTask('less-compile', ['less:compileCore', 'autoprefixer:core', 'usebanner', 'cssmin:minifyCore']);
    
    // Distribute all assets to public folder
    grunt.registerTask('dist-assets', ['copy:fonts', 'copy:jquery', 'copy:bootstrapjs', 'copy:jqueryui', 'copy:jqueryuijs']);
    
    // Default task, compile and distribute all assets to public folder
    grunt.registerTask('default', ['less-compile', 'dist-assets']);
};