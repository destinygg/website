const webpack = require('webpack');
const path = require('path');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const CleanWebpackPlugin = require('clean-webpack-plugin');

module.exports = {
    entry: {
        web       : './assets/web.js',
        admin     : './assets/admin.js',
        profile   : './assets/profile.js',
        chat      : './assets/chat.js',
        streamchat: './assets/streamchat.js'
    },
    output: {
        path     : __dirname + '/static',
        filename : '[name].js'
    },
    plugins: [
        new CleanWebpackPlugin(['static'], {root: __dirname, verbose: false, exclude: ['cache']}),
        new ExtractTextPlugin({filename: '[name].css'})
    ],
    watchOptions: {
        ignored: /node_modules/
    },
    module: {
        rules: [
            {
                test    : /\.(ts|tsx)$/,
                loader  : 'ts-loader'
            },
            {
                test    : /\.json$/,
                loader  : 'json-loader'
            },
            {
                test    : /\.js$/,
                exclude : /(node_modules)/,
                loader  : 'babel-loader',
                options : {presets: ['es2015']}
            },
            {
                test    : /\.(scss|css)$/,
                loader  : ExtractTextPlugin.extract({
                    fallback: 'style-loader',
                    use: [
                        {loader: 'css-loader'},
                        {loader: 'sass-loader'},
                        {loader: 'postcss-loader'},
                    ]
                })
            },
            {
                test    : /(-webfont|glyphicons-halflings-regular)\.(eot|svg|ttf|woff2?)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
                loader  : 'file-loader',
                options : {name: 'fonts/[name].[ext]'}
            },
            {
                test    : /\.(png|jpg|gif)$/,
                loader  : 'file-loader',
                options : {name: 'img/[name].[ext]'}
            }
        ]
    },
    resolve: {
        alias: {
            jquery: 'jquery/src/jquery'
        },
        extensions: ['.ts','.tsx','.js']
    },
    context: __dirname,
    devtool: false
};