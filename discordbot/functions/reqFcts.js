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
            if (body == "")
                return console.log("Weppage is empty");
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
                                .setAuthor({ name: "\u200B" + other.convertToReadable(msgtosend[msgantinb].sender.substring(0, 200)) })
                                .setDescription("\u200B" + other.convertToReadable(msgtosend[msgantinb].content.substring(0, 4000)).split("§").join(" "))

                            var isCertified = parseInt(msgtosend[msgantinb].id_certified_user)

                            if (isCertified && isCertified > 0) {
                                embed.setColor(0x58ce58)
                                embed.setFooter({ text: "✅ Utilisateur certifié | " + msgtosend[msgantinb].date_time });
                            }
                            else {
                                embed.setColor(0xccaf13)
                                embed.setFooter({ text: "⚠️ Utilisateur non certifié | " + msgtosend[msgantinb].date_time });
                            }
                            // zone easter egg
                            if (isCertified && isCertified == 1) {
                                embed.setColor(0xdf1010)
                                embed.setFooter({ text: "✅ Utilisateur certifié par lui même | " + msgtosend[msgantinb].date_time });
                            }
                            else if (isCertified && isCertified == 2) {
                                embed.setColor(0xdf1010)
                                embed.setFooter({ text: "✅ Utilisateur certifié Melon Musk | " + msgtosend[msgantinb].date_time });
                            }
                            else if (isCertified && isCertified == 3) {
                                embed.setColor(0x1091df)
                                embed.setFooter({ text: "✅ Utilisateur certifié [BOT]| " + msgtosend[msgantinb].date_time });
                            }


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
    sendMsg: function (sender, msgtosend, incognito = false) {
        if (!incognito) {
            msgtosend = `${sender.split(" ").join("_").substring(0, 23)} : ${msgtosend}`
            var opts = {
                url: encodeURI(url + `message=${msgtosend.split(" ").join("§").substring(0, 252)}&sender=Bot&token=${process.env.MSTKBOT}`),
                timeout: timeoutInMilliseconds,
                encoding: "utf-8"
            }
        }
        else
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