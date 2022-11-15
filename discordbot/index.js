const debugmsg = require("./config/admin/debugmsg.json")
console.log(debugmsg.init.startInitMsg);
// le code est dégueu mais osef au pire

// Import libraries
const fs = require('fs');
const path = require('path');
const request = require('request')

// Import configurations
const initcfg = require("./config/admin/init.json");
var channelsList = require("./config/public/channelsChat.json");
var settings = require("./config/admin/settings.json")

const { Client, Partials, SlashCommandBuilder, REST, Routes, Permissions, GuildExplicitContentFilter, EmbedBuilder } = require('discord.js');
// Client creation and export
const client = new Client({
    intents: initcfg.intents,
    partials: initcfg.partials
});
const clientId = initcfg.idbot;
exports.client = client;

// functions

function updatecfg(file, content, muted = false) {
    const jsonStringcfg = JSON.stringify(content);
    fs.writeFile(file, jsonStringcfg, err => {
        if (err) {
            if (!muted) console.log(`Error writing ${file}`, err)
        } else {
            if (!muted) console.log(`Successfully wrote ${file}`)
        }
    })
}

function arrayRemove(arr, value) {
    return arr.filter(function (ele) {
        return ele != value;
    });
}
function convertToReadable(string) {
    string = string.split("&quot;").join('"')
    return string.replace(/&#(?:x([\da-f]+)|(\d+));/ig, function (_, hex, dec) {
        return String.fromCharCode(dec || +('0x' + hex))
    })
}

// Slash commands
const rest = new REST({ version: '10' }).setToken(process.env.TOKEN);

let commandslash = []

commandslash.push(new SlashCommandBuilder()
    .setName('send')
    .setDescription("Permet d'envoyer un message sur le MicaSend")
    .addStringOption(option =>
        option.setName('message')
            .setDescription('Le message à envoyer')
            .setRequired(true)
            .setMinLength(1)
            .setMaxLength(250)
    ));


commandslash.push(new SlashCommandBuilder()
    .setName('addchannel')
    .setDescription("Permet de recevoir TOUS les messages du MicaSend sur ce channel"));

commandslash.push(new SlashCommandBuilder()
    .setName('remchannel')
    .setDescription("Permet de ne plus recevoir aucun message du MicaSend sur ce channel"));

commandslashjson = commandslash.map(command => command.toJSON());

(async () => {
    try {
        console.log(`Started refreshing ${commandslashjson.length} application (/) commands.`);

        const data = await rest.put(
            Routes.applicationCommands(clientId),
            { body: commandslashjson },
        );

        console.log(`Successfully reloaded ${data.length} application (/) commands.`);
    } catch (error) {
        // And of course, make sure you catch and log any errors!
        console.error(error);
    }
})();


// request config
// use a timeout value of 10 seconds
const url = 'https://micasend.baptiste-reb.fr/msg.php?'
const timeoutInMilliseconds = 10 * 1000





// when Bot logged in Discord
client.once('ready', () => {
    setInterval(() => {
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
            //console.log('status code: ' + statusCode)
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
                                .setDescription("\u200B" + convertToReadable(msgtosend[msgantinb].content.substring(0, 4000)).split("§").join(" "))

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
                            updatecfg(`./config/public/channelsChat.json`, channelsList)
                        }
                    }
                } catch (error) {
                    console.log("Ce channel n'a pas l'air d'exister : " + channelid);
                }

            }
        })
    }, settings.refreshtime);

    setInterval(() => {
        statustype = Math.floor(Math.random() * 4) + 1;
        if (statustype === 1) {
            client.user.setActivity("MICASEND !", { type: 'LISTENING' });
        }
        else if (statustype >= 2) {
            client.user.setActivity("/send", { type: 'PLAYING' });
        }
    }, 30000);

    console.log(debugmsg.init.endInitMsg);


});

// Prevents bot from crash
process.on('uncaughtException', function (err) {
    console.error(err);

});

client.on('interactionCreate', interaction => {
    if (interaction.isCommand()) {
        try {
            if (interaction.commandName === 'send') {
                //console.log("requesting page")
                const msgtosend = interaction.options.getString('message') ?? 'No message provided';
                var opts = {
                    url: encodeURI(url + `message=${msgtosend.split(" ").join("§").substring(0, 252)}&sender=${interaction.user.username.split(" ").join("_").substring(0, 23)}`),
                    timeout: timeoutInMilliseconds,
                    encoding: "utf-8"
                }
                request(opts, function (err, res, body) {
                    if (err) {
                        console.dir(err)
                        return interaction.reply({ content: "Le message \n```" + msgtosend + "```\nn'a pas pu être envoyé, ERREUR : " + res.statusCode, ephemeral: true });
                    }
                    var statusCode = res.statusCode
                    console.log(`Message sent by ${interaction.user.id}`, 'status code: ' + statusCode)
                })
                return interaction.reply({ content: "Récapitulatif du message envoyé : \n```" + msgtosend + "```", ephemeral: true });

            }




            // setup config
            else if (interaction.commandName === 'addchannel') {
                if (interaction.user.id != "444579657279602699" && interaction.member.permissions.has(Permissions.FLAGS.MANAGE_MESSAGES))
                    return interaction.reply({ content: "Tu n'es pas modérateur sur ce serveur !", ephemeral: true });

                channelsList[interaction.channel.id] = {
                    sendMsgInIt: 1,
                    lastmsg: Date.now()
                };
                updatecfg(`./config/public/channelsChat.json`, channelsList)
                interaction.reply({ content: `Le salon <#${interaction.channel.id}> a bien été ajouté`, ephemeral: true });
            }

            else if (interaction.commandName === 'remchannel') {
                if (interaction.user.id != "444579657279602699" && interaction.member.permissions.has(Permissions.FLAGS.MANAGE_MESSAGES))
                    return interaction.reply({ content: "Tu n'es pas modérateur sur ce serveur !", ephemeral: true });

                channelsList[interaction.channel.id].sendMsgInIt = 0;
                updatecfg(`./config/public/channelsChat.json`, channelsList)
                interaction.reply({ content: `Le salon <#${interaction.channel.id}> a bien été supprimé de la liste des mises à jour`, ephemeral: true });
            }
        } catch (error) {
            interaction.reply({ content: "Une erreur est survenue lol", ephemeral: true });
        }

    }
})

client.login(process.env.TOKEN);