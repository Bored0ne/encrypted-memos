var gulp = require('gulp');
var fs = require('fs-extra');
var del = require('del');
var pack = require('./package.json');
var exec = require('child_process').execSync;

var paths = {
	www: 'www/',
	cordova: 'cordova/',
	cordovaFiles: 'cordova/*',
	cordovaWWW: 'cordova/www/',
	electron: 'electron/',
	electronFiles: 'electron/*',
	electronWWW: 'electron/www/'
};

var isFirstTime = function () {
	return !fs.existsSync(paths.cordovaWWW) && !fs.existsSync(paths.electronWWW);
}

var makeIfNotExist = function($dir) {
	if (!fs.existsSync($dir)){
		fs.mkdirSync($dir);
	}
}

var copyWWW = function() {
	// console.log(paths.cordovaWWW);
	fs.copySync('www', paths.cordovaWWW, {clobber: true, dereference: true, preserveTimestamps: true}, function (err){
		if (err) console.log(err);
	});
	fs.copySync('www', paths.electronWWW, {clobber: true, dereference: true, preserveTimestamps: true}, function (err){
		if (err) console.log(err);
	});
}

var deleteWWW = function () {
	console.log('deleting cordova www files: ' + del(paths.cordovaWWW + '*'));
	console.log('deleting electron www files: ' + del(paths.electronWWW + '*'));
}

var wipe = function() {
	return del([paths.cordova, paths.electron]);
}

var refreshAll = function() {
	deleteWWW();
	copyWWW();
}

gulp.task('clean', function() {
	return deleteWWW();
})

gulp.task('wipe', function() {
	return wipe();
});

gulp.task('refresh', function() {
	refreshAll();
})

gulp.task('build', function() {
	// makeIfNotExist(paths.cordova);
	makeIfNotExist(paths.electron);
	if (isFirstTime()) {
		console.log('Please be gentle senpai! It\'s my first time!');
		wipe();
		// run cordova install and electron install
		// shell('cd ' + paths.cordova + ' && cordova create ' + pack.name );
		exec('cordova create cordova && cd cordova && cordova platform add ios', {timeout: 100000}, function (err, stdout, stderr){
			console.log(err + stderr + stdout);
		});
		fs.copy('.resources/main.js', paths.electron + 'main.js', {timeout: 100000}, function (err){
			if (err) return console.error(err)
		});
	}
	refreshAll();
});

gulp.task('serve-electron', ['build'], function() {
		exec('cd ' + paths.electron + ' && electron main.js &', {detached: true}, function (err, stdout, stderr){
			console.log(stdout);
		});
});

gulp.task('serve-cordova', ['build'], function() {
		exec('cd ' + paths.cordova + ' && cordova run ios &', {timeout: 100000}, function (err, stdout, stderr){
			console.log(stdout);
		});
});
