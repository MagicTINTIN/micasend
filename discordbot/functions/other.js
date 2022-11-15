module.exports = {
    convertToReadable: function (string) {
        string = string.split("&quot;").join('"')
        return string.replace(/&#(?:x([\da-f]+)|(\d+));/ig, function (_, hex, dec) {
            return String.fromCharCode(dec || +('0x' + hex))
        })
    }
}