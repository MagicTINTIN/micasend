const debugmsg = require("./config/admin/debugmsg.json")
console.log(debugmsg.init.startInitMsg);
// le code est dégueu mais osef au pire

// Import configurations
const initcfg = require("./config/admin/init.json")

const { Client, Partials, SlashCommandBuilder, REST, Routes } = require('discord.js');
// Client creation and export
const client = new Client({
    intents: initcfg.intents,
    partials: initcfg.partials
});
const clientId = "1041841555729821707";
exports.client = client;

const rest = new REST({ version: '10' }).setToken(process.env.TOKEN);

let commandslash = []

commandslash.push(new SlashCommandBuilder()
    .setName('send')
    .setDescription("Permet d'envoyer un message sur le MicaSend")
    .addStringOption(option =>
        option.setName('message')
            .setDescription('Le message à envoyer')
            .setRequired(true)));

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


// when Chaline logged in Discord
client.once('ready', () => {
    console.log(debugmsg.init.endInitMsg);
});

// Prevents bot from crash
process.on('uncaughtException', function (err) {
    console.error(err);

});

client.on('interactionCreate', interaction => {
    if (interaction.isCommand()) {
        if (interaction.commandName === 'send') {
            console.log("requesting page")
            const msgtosend = interaction.options.getString('message') ?? 'No message provided';
            return interaction.reply({ content: "Le système MicaSend n'est pas encore opérationnel *(soon)*\nRécapitulatif du message : \n```" + msgtosend + "```", ephemeral: true });
        }
    }
})

client.login(process.env.TOKEN);