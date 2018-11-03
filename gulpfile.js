const gulp         = require('gulp');
const autoprefixer = require('gulp-autoprefixer');
const babel        = require('gulp-babel');
const cleanCSS     = require('gulp-clean-css');
const less         = require('gulp-less');
const plumber      = require('gulp-plumber');
const rename       = require('gulp-rename');
const sass         = require('gulp-sass');
const sourcemaps   = require('gulp-sourcemaps');
const typescript   = require('gulp-typescript');
const uglify       = require('gulp-uglify');

const src  = './assets/';
const dest = './public/';

gulp.task('sass', () =>
{
    gulp
        .src(src + 'sass/**/[^_]*.s[ac]ss')
        .pipe(sourcemaps.init())
            .pipe(plumber())
            .pipe(sass())
            .pipe(autoprefixer())
            .pipe(cleanCSS())
            .pipe(rename({suffix: '.min'}))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(dest + 'css'));
});

gulp.task('less', () =>
{
    gulp
        .src(src + 'less/**/[^_]*.less')
        .pipe(sourcemaps.init())
            .pipe(plumber())
            .pipe(less())
            .pipe(autoprefixer())
            .pipe(cleanCSS())
            .pipe(rename({suffix: '.min'}))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(dest + 'css'));
});

gulp.task('es6', () =>
{
    gulp
        .src(src + 'babel/**/[^_]*.es6')
        .pipe(sourcemaps.init())
            .pipe(plumber())
            .pipe(babel(
            {
                sourceType: 'script',
                presets   : ['@babel/preset-env']
            }))
            .pipe(uglify())
            .pipe(rename({suffix: '.min'}))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(dest + 'js'));
});

const tsProject = typescript.createProject('tsconfig.json');

gulp.task('ts', () =>
{
    tsProject
        .src()
        .pipe(sourcemaps.init())
            .pipe(plumber())
            .pipe(tsProject())
            .js
            .pipe(uglify())
            .pipe(rename({suffix: '.min'}))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest(dest + 'js'));
});

gulp.task('default', () =>
{
    gulp.watch([src + 'sass/**/*.s[ac]ss'], ['sass']);
    // gulp.watch([src + 'less/**/*.less'], ['less']);
    gulp.watch([src + 'babel/**/*.es6'], ['es6']);
    gulp.watch([src + 'typescript/scripts/**/*.ts'], ['ts']);
});
