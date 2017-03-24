const webpack = require('webpack');
const path = require('path');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const CleanWebpackPlugin = require('clean-webpack-plugin');

module.exports = {
    entry: {
        common: [
            'core-js/es6',
            'jquery',
            'moment',
            'font-awesome/scss/font-awesome.scss',
            './assets/fonts/roboto.scss',
            'bootstrap/dist/css/bootstrap.css',
            'bootstrap/dist/js/bootstrap.js'
        ],
        web       : './assets/web.js',
        admin     : './assets/admin.js',
        messages  : './assets/messages.js',
        chat      : './assets/chat.js',
        streamchat: './assets/streamchat.js'
    },
    output: {
        path     : __dirname + '/static',
        filename : '[name].js'
    },
    plugins: [
        new CleanWebpackPlugin(['static'], {root: __dirname, verbose: false, exclude: ['cache']}),
        new webpack.ProvidePlugin({'$': 'jquery', 'jQuery': 'jquery', 'window.jQuery': 'jquery'}),
        new webpack.optimize.DedupePlugin(),
        new webpack.optimize.OccurenceOrderPlugin(),
        new webpack.optimize.CommonsChunkPlugin('common', 'common.js'),
        new ExtractTextPlugin('[name].css')
    ],
    resolve: {
        alias: {
            jquery: 'jquery/src/jquery'
        },
        extensions: ['', '.ts', '.tsx', '.js']
    },
    module: {
        loaders: [
            {
                test    : /\.(ts|tsx)$/,
                loader  : 'ts-loader'
            },
            {
                test    : /\.json$/,
                loader  : 'json'
            },
            {
                test    : /\.js$/,
                exclude : /(node_modules)/,
                loader  : 'babel?presets[]=es2015'
            },
            {
                test    : /\.(scss|css)$/,
                loader  : ExtractTextPlugin.extract('style', 'css!sass')
            },
            {
                test    : /(-webfont|glyphicons-halflings-regular)\.(eot|svg|ttf|woff2?)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
                loader  : 'file-loader?name=fonts/[name].[ext]'
            },
            {
                test    : /\.(png|jpg|gif)$/,
                loader  : 'file-loader?name=img/[name].[ext]'
            }
        ]
    },
    context: __dirname,
    devtool: null//'source-map'
};