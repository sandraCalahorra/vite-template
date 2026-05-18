const mix = require('laravel-mix')
const path = require('path')
const webpack = require('webpack')

mix.options({
    notify: false
  });

mix.disableNotifications();

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .options({
        processCssUrls: false,
        postCss: [require("tailwindcss")]
    })
    .postCss('resources/css/app.css', 'public/css', [
        require("tailwindcss"),
    ])
    .vue();

mix.webpackConfig({
    output: {
        chunkFilename: 'js/[name].js',
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js/')
        },
        fallback: {
            crypto: require.resolve('crypto-browserify'),
            stream: require.resolve('stream-browserify'),
            "vm": require.resolve("vm-browserify")
        },
    },
    plugins: [
        new webpack.DefinePlugin({
            '__VUE_PROD_HYDRATION_MISMATCH_DETAILS__': JSON.stringify(true),
            // Define other flags here if needed
        }),
    ],
});
