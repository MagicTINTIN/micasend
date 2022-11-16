const debugmsg = require("./config/admin/debugmsg.json")
console.log(debugmsg.init.startInitMsg);
// le code est dégueu mais osef au pire


// Import configurations
const initcfg = require("./config/admin/init.json");
var settings = require("./config/admin/settings.json")

// import librairies
const { Client, Partials, SlashCommandBuilder, REST, Routes, Permissions, GuildExplicitContentFilter, EmbedBuilder } = require('discord.js');
const reqFcts = require("./functions/reqFcts");
const slashcmd = require("./functions/slashcmd");
const fs = require('fs');

// Client creation and export
const client = new Client({
    intents: initcfg.intents,
    partials: initcfg.partials
});
const clientId = initcfg.idbot;
exports.client = client;

slashcmd.initSlash(process.env.TOKEN, clientId)

function checkMsg(compteur) {

}

// when Bot logged in Discord
client.once('ready', () => {

    var compteur = 0

    reqLoop = setInterval(() => {
        var isSlow = JSON.parse(fs.readFileSync(`./config/admin/slowmode.json`))
        if (isSlow.on && isSlow.on == true && compteur < 12) {
            compteur++
            //console.log("skip slow nb " + compteur);
        }
        else {
            reqFcts.actuMsg(client)
            compteur = 0;
        }
    }, settings.refreshtime);
    client.user.setActivity("MicaSenderBot restarting", { type: 'PLAYING' });
    statusLoop = setInterval(() => {

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

process.on('uncaughtException', function (err) {
    console.error(err);

});

client.on('interactionCreate', interaction => {
    if (interaction.isCommand()) {
        slashcmd.onSlash(interaction);
    }
})

client.login(process.env.TOKEN);