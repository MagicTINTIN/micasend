// Import libraries
const fs = require('fs');

module.exports = {

    updatecfg: function (file, content, muted = false) {
        const jsonStringcfg = JSON.stringify(content);
        fs.writeFile(file, jsonStringcfg, err => {
            if (err) {
                if (!muted) console.log(`Error writing ${file}`, err)
            } else {
                if (!muted) console.log(`Successfully wrote ${file}`)
            }
        })
    }
}