// Import libraries
const request = require('request')
const { EmbedBuilder } = require('discord.js');
const other = require('./other');
const saver = require('./saver');

// timeout value 10 seconds
const url = 'https://micasend.baptiste-reb.fr/msg.php?'
const timeoutInMilliseconds = 10 * 1000

module.exports = {
    actuMsg: function (client) {
        var channelsList = require("../config/public/channelsChat.json");
        var opts = {
            url: encodeURI(url + `getmsg=json`),
            timeout: timeoutInMilliseconds,
            encoding: "utf-8"
        }

        request(opts, function (err, res, body) {
            if (err) {
                console.dir(err)
                return
            }

            const listOfMessages = JSON.parse(body)
            for (const channelid in channelsList) {
                try {
                    const channeltosend = client.channels.cache.get(channelid);
                    if (channelsList[channelid].sendMsgInIt == 1 && channeltosend) {

                        var endactu = false;
                        var msgNb = listOfMessages.length - 1;
                        var msgtosend = []
                        // messages are sorted in reverse
                        while (!endactu && msgNb >= 0) {
                            if (Date.parse(listOfMessages[msgNb].date_time) > channelsList[channelid].lastmsg)
                                msgtosend.push(listOfMessages[msgNb])
                            else
                                endactu = true
                            msgNb--;
                        }
                        // messages are send in correct order
                        for (let msgantinb = msgtosend.length - 1; msgantinb >= 0; msgantinb--) {
                            const embed = new EmbedBuilder()
                                .setAuthor({ name: "\u200B" + msgtosend[msgantinb].sender.substring(0, 200) })
                                .setDescription("\u200B" + other.convertToReadable(msgtosend[msgantinb].content.substring(0, 4000)).split("§").join(" "))

                            if (true)
                                embed.setColor(0xccaf13)
                            if (true)
                                embed.setFooter({ text: "⚠️ La certification de l'utilisateur n'a pas pu être vérifiée" }); // sent by a bot or by terminal
                            try {
                                channeltosend.send({ embeds: [embed] });
                            } catch (error) {
                                console.log("Ce channel n'a pas l'air de fonctionner : " + channelid);
                            }
                        }
                        if (msgtosend.length > 0) {
                            channelsList[channelid].lastmsg = Date.parse(msgtosend[msgtosend.length - 1].date_time) + 1
                            saver.updatecfg(`./config/public/channelsChat.json`, channelsList, true)
                        }
                    }
                } catch (error) {
                    console.log("Ce channel n'a pas l'air d'exister : " + channelid);
                }

            }
        })
    },


    // request to send message
    sendMsg: function (sender, msgtosend) {
        var opts = {
            url: encodeURI(url + `message=${msgtosend.split(" ").join("§").substring(0, 252)}&sender=${sender.split(" ").join("_").substring(0, 23)}`),
            timeout: timeoutInMilliseconds,
            encoding: "utf-8"
        }
        request(opts, function (err, res, body) {
            if (err) {
                console.dir(err)
                return 1
            }
            var statusCode = res.statusCode
            console.log(`Message sent by ${sender}`, 'status code: ' + statusCode)
            return 0
        })
    }
}