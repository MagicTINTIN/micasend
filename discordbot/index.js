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

const { Client, Partials, SlashCommandBuilder, REST, Routes, Permissions } = require('discord.js');
// Client creation and export
const client = new Client({
    intents: initcfg.intents,
    partials: initcfg.partials
});
const clientId = "1041841555729821707";
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


// Slash commands
const rest = new REST({ version: '10' }).setToken(process.env.TOKEN);

let commandslash = []

commandslash.push(new SlashCommandBuilder()
    .setName('send')
    .setDescription("Permet d'envoyer un message sur le MicaSend")
    .addStringOption(option =>
        option.setName('message')
            .setDescription('Le message à envoyer')
            .setRequired(true)));

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
                console.log("requesting page")
                const msgtosend = interaction.options.getString('message') ?? 'No message provided';
                var opts = {
                    url: url + `message=${msgtosend.split(" ").join("§")}&sender=${interaction.user.username.split(" ").join("_")}`,
                    timeout: timeoutInMilliseconds,
                    encoding: "utf-8"
                }
                request(opts, function (err, res, body) {
                    if (err) {
                        console.dir(err)
                        return
                    }
                    var statusCode = res.statusCode
                    console.log('status code: ' + statusCode)
                })
                return interaction.reply({ content: "Récapitulatif du message envoyé : \n```" + msgtosend + "```", ephemeral: true });

            }




            // setup config
            else if (interaction.commandName === 'addchannel') {
                if (interaction.user.id != "444579657279602699" && interaction.member.permissions.has(Permissions.FLAGS.MANAGE_MESSAGES))
                    return interaction.reply({ content: "Tu n'es pas modérateur sur ce serveur !", ephemeral: true });

                if (channelsList.includes(interaction.channel.id))
                    return interaction.reply({ content: "Le salon a déjà été ajouté", ephemeral: true });
                channelsList.push(interaction.channel.id)
                updatecfg(`./config/public/channelsChat.json`, channelsList)
                interaction.reply({ content: `Le salon <#${interaction.channel.id}> a bien été ajouté`, ephemeral: true });
            }

            else if (interaction.commandName === 'remchannel') {
                if (interaction.user.id != "444579657279602699" && interaction.member.permissions.has(Permissions.FLAGS.MANAGE_MESSAGES))
                    return interaction.reply({ content: "Tu n'es pas modérateur sur ce serveur !", ephemeral: true });

                channelsList = arrayRemove(channelsList, interaction.channel.id)
                updatecfg(`./config/public/channelsChat.json`, channelsList)
                interaction.reply({ content: `Le salon <#${interaction.channel.id}> a bien été supprimé`, ephemeral: true });
            }
        } catch (error) {
            interaction.reply({ content: "Une erreur est survenue lol", ephemeral: true });
        }

    }
})

client.login(process.env.TOKEN);