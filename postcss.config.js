module.exports = {
    plugins: [
        require('autoprefixer')({
            // Bootstrap 5.3 targets current evergreen browsers
            overrideBrowserslist: ['defaults', 'not dead', 'not ie 11']
        })
    ]
};
