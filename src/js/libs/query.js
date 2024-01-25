// Query Object -----------------------------------------------------------------------------------------------------------------

import {MAIN_URL} from "../const";

const MAIN_PHP_PATH = MAIN_URL + 'main.php';

const checkJSON = (data) => {
  try {
    const response = JSON.parse(data);
    if (response['error']) throw response['error'];
    return response;
  }
  catch (e) {
    const msg = e['xdebug_message'] || e.message || e;
    msg && console.error(msg);
    data && console.error(data);
    return {status: false};
  }
};

const getFileName = (data) => {
  let fileName = data.headers.get('Content-Disposition');

  if (typeof fileName === 'string') {
    const match = /(?:filename=")\w+\.\w+(?=")/i.exec(fileName); // safari error
    fileName = Array.isArray(match) && match.length === 1 && match[0];
    if (fileName.replace) fileName = fileName.replace('filename="', '');
  }

  return fileName || JSON.parse(data.headers.get('fileName')) || '';
}
const downloadBody = async (data) => {
  const fileName = getFileName(data),
        reader   = data.body.getReader();
  let chunks    = [],
      countSize = 0;

  while (true) {
    // done становится true в последнем фрагменте
    // value - Uint8Array из байтов каждого фрагмента
    const {done, value} = await reader.read();

    if (done) break;

    chunks.push(value);
    countSize += value.length;
  }
  return Object.assign(new Blob(chunks), {fileName});
}

const query = (url, body, type = 'json') => {
  const headers = {
    'Cookie': document.cookie,
  };

  if (body && ['object', 'string'].includes(typeof body) && !(body instanceof FormData)) {
    let data = new FormData();

    if (typeof body === 'object') {
      Object.entries(body).forEach(([k, v]) => {
        if (!v) return;
        if (v instanceof Blob) data.append(k, v, v.name);
        else data.set(k, typeof v === 'object' ? JSON.stringify(v) : v.toString());
      });
    }
    else data.set('content', body);

    const v = localStorage.getItem('support-user-key');
    if (v && v !== 'null') data.set('support-user-key', v);

    body = data;
  }

  type === 'file' && (type = 'body');
  return fetch(url, {method: 'post', headers, credentials: "same-origin", body})
    .then(res => type === 'json' ? res.text() : res).then(
      data => {
        if (type === 'json') return checkJSON(data);
        else if (type === 'body') return downloadBody(data);
        else return data[type]();
      },
      error => console.log(error),
    );
};

/**
 * Query namespace
 * @const
 * @type {{Post: function, Get: function}}
 * @function Post({url: String, data, type})
 */
export default {

  /**
   * Fetch Get
   * @param {object} obj
   * @param {string?|any?: c.MAIN_PHP_PATH} obj.url - link to index.php.
   * @param {string|Object} obj.data - get params as string.
   * @param {string?: 'json'} obj.type - return type.
   * @return {Promise<string | void>}
   * @constructor
   */
  Get: ({url = MAIN_PHP_PATH, data, type = 'json'}) =>
    query(url + '?' + (typeof data === 'string' ? data : (new URLSearchParams(data)).toString()), null, type),

  /**
   * Fetch Post
   * @param {object} obj
   * @param {string?|any?: c.MAIN_PHP_PATH} obj.url - link to index.php.
   * @param {BodyInit|Object} obj.data -
   * Any body that you want to add to your request object.
   * Note that a request using the GET or HEAD method cannot have a body.
   * @param {string?: 'json'} obj.type - return type.
   * @return {Promise<string | void>}
   */
  Post: ({url = MAIN_PHP_PATH, data, type = 'json'}) => query(url, data, type),
};
