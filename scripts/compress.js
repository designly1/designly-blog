const { compress } = require('brotli');
const { readFileSync, writeFileSync, readdirSync } = require('fs');
const { join, extname } = require('path');

// list of extensions to compress
const exts = ['.js', '.css', '.html', '.map'];

// directory with the assets
const htmlDir = join(__dirname, '../', 'public_html');
const assetsDir = join(htmlDir, 'assets');
const cssDir = join(assetsDir, 'css');

const compressDirs = [cssDir, htmlDir];

const doCompress = (dir) =>
    readdirSync(dir)
        .filter(file => exts.includes(extname(file)))
        .forEach(file => {
            const filePath = join(dir, file);
            console.log(`Compressing ${filePath}`);
            const data = readFileSync(filePath);
            const compressed = compress(data);
            const outFile = `${filePath}.br`;
            writeFileSync(outFile, compressed);
        });

compressDirs.forEach(doCompress);