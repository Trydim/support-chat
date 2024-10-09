const path    = require('path'),
      webpack = require('webpack'),
      { VueLoaderPlugin } = require('vue-loader');

const //HtmlWebpackPlugin = require("html-webpack-plugin"),
      MiniCssExtractPlugin = require('mini-css-extract-plugin');
//const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

module.exports = env => {
  const dev = !env.production;

  return {
    mode        : dev ? 'development' : 'production',
    watch       : dev, // слежка за изменениями файлов
    watchOptions: {aggregateTimeout: 300}, // задержка оценки изменений в мс
    entry       : {
      supportApp: './js/supportApp.js',
      support   : './js/support.js',
    },

    experiments: {
      outputModule: true,
    },

    output : {
      path         : path.resolve(__dirname, '../../'),
      publicPath   : '/',
      filename     : dev ? '[name].js' : '[name].min.js',
      scriptType   : 'module',
      module       : true,
      libraryTarget: 'module',
    },
    resolve: {
      extensions: ['.js', '.jsx', '.json', '.vue'],
      alias: {
        vue: dev ? 'vue/dist/vue.esm-bundler.js' : 'vue/dist/vue.esm-browser.prod.js',
      }
    },

    devtool: dev ? 'source-map' : false, //source mapping
    optimization: {
      /*splitChunks: {
        chunks: 'all',
        cacheGroups: {
          defaultVendors: {
            test: /konva/,
            priority: -10,
            reuseExistingChunk: true,
          },
        },
      },*/
      minimize : !dev,

      minimizer: [`...`],
    },
    plugins: [
      new VueLoaderPlugin(),

      new webpack.DefinePlugin({
        // Drop Options API from bundle
        __VUE_OPTIONS_API__  : 'true',
        __VUE_PROD_DEVTOOLS__: 'false',
        __VUE_PROD_HYDRATION_MISMATCH_DETAILS__: false,
      }),

      //new BundleAnalyzerPlugin(),
    ],
    module: {
      rules: [
        getVueRules(),
        getScssRules(),
        getCssRules(),
        getImageRules(),
        getSVGRules(),
        getFontsRules(),
        getOtherFilesRules(),
      ]
    },

    devServer: {
      static: {
        directory: path.join(__dirname, '/'),
      },
      port: 3000,
      hot: false,
      open: true,
    },
  };
};

// ---------------------------------------------------------------------------------------------------------------------
// Правила / Rules
// ---------------------------------------------------------------------------------------------------------------------

/** asset/resource - file-loader - в отдельный файл
 * asset/inline - url-loader - inline базе64
 * asset/source - raw-loader - ?
 * asset - автоматический выбор от размера по умолчанию 8к */

/**
 * Vue
 * @return {object}
 */
const getVueRules = () => ({
  test  : /\.vue$/,
  loader: "vue-loader"
});

/**
 * css-loader
 * @return {object}
 */
const getCssLoader = () => ({
  loader: 'css-loader',
  options: {
  },
});

/**
 * Scss
 * @return {object}
 */
const getScssRules = () => ({
  test: /\.s[ac]ss$/i,
  use : [
    //MiniCssExtractPlugin.loader,
    getCssLoader(),
    'sass-loader',
  ],
});

/**
 * Css
 * @return {object}
 */
const getCssRules = () => ({
  test: /\.css$/i,
  use : [
    //MiniCssExtractPlugin.loader,
    getCssLoader(),
  ],
});

/**
 * Image
 * loader: 'svgo-loader', - какой-то инлайн лоадер
 * @return {object}
 */
const getImageRules = () => ({
  test   : /\.(png|jpe?g|gif|webp)$/i,
  type   : 'asset',
  generator: {
    filename: 'image/[name][ext]',
  },
  parser: {
    dataUrlCondition: {
      maxSize: 8196, // 8kb
    }
  },
});

/**
 * SVG
 * inline
 * @return {object}
 */
const getSVGRules = () => ({
  test: /\.(svg)$/,
  type: 'asset',
  generator: {
    filename: 'svg/[name][ext]',
  },
  parser: {
    dataUrlCondition: {
      maxSize: 8196, // 8kb
    }
  },
});

/**
 * Шрифты
 * @return {object}
 */
const getFontsRules = () => ({
  test   : /\.(ttf|woff|woff2|eot)$/,
  type   : "asset/resource",
  generator: {
    filename: 'fonts/[name][ext]',
  },
});

/* Прочее */
const getOtherFilesRules = () => ({
  test   : /config.json$/i,
  type   : 'asset/resource',
  generator: {
    filename: 'static/[name][ext]',
  },
});
