let webpack = require('webpack');
let path = require('path');
let ExtractTextPlugin = require('extract-text-webpack-plugin');
let CleanWebpackPlugin = require('clean-webpack-plugin');

module.exports = {
    entry: {
        common: ['jquery','moment','font-awesome/scss/font-awesome.scss'],
        web: './assets/web.js',
        chat: './assets/chat.js',
        admin: './assets/admin.js'
    },
    output: {
        path: __dirname + '/static',
        filename: '[name].js'
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
                test: /\.json$/,
                loader: 'json'
            },
            {
                test: /\.(scss|css)$/,
                loader: ExtractTextPlugin.extract('style', 'css!sass')
            },
            {
                test   : /(-webfont|glyphicons-halflings-regular)\.(eot|svg|ttf|woff2?)(\?v=[0-9]\.[0-9]\.[0-9])?$/,
                loader : 'file-loader?name=fonts/[hash].[ext]'
            },
            {
                test   : /\.(png|jpg|gif)$/,
                loader : 'file-loader?name=img/[hash].[ext]'
            },
            {
                test: /\.tsx?$/,
                loader: 'ts-loader'
            }
        ]
    },
    context: __dirname,
    devtool: null
};