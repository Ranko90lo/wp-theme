const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = async () => {
    const { default: SVGSpriteMapPlugin } = await import('svg-spritemap-webpack-plugin');

    const sharedConfig = {
        output: {
            path: path.resolve(process.cwd(), 'dist'),
            filename: 'js/[name].js',
            chunkFilename: 'js/[name].js',
        },
        module: {
            ...defaultConfig.module,
            rules: [
                {
                    test: /\.s[ac]ss$/i,
                    use: [
                        MiniCssExtractPlugin.loader,
                        'css-loader',
                        'sass-loader',
                    ],
                },
            ],
        },
        plugins: [
            new MiniCssExtractPlugin({
                filename: 'css/[name].css',
            }),
            new SVGSpriteMapPlugin(['assets/icons/src/**/*.svg'], {
                output: {
                    filename: 'icons/sprite.svg',
                },
                sprite: {
                    prefix: 'icon-',
                },
            }),
        ],
    };

    return [
        {
            ...defaultConfig,
            ...sharedConfig,
            entry: {
                main: [
                    './assets/js/src/index.js',
                    './assets/css/src/index.scss',
                ],
            },
        },
    ];
};
