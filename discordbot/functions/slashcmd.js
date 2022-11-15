// Import libraries
const { SlashCommandBuilder, REST, Routes, Permissions } = require('discord.js');
const reqFcts = require('./reqFcts');
const saver = require('./saver');

module.exports = {
    initSlash: function (token, clientId) {
        // Slash commands
        const rest = new REST({ version: '10' }).setToken(token);

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
                // errors while loading (/)
                console.error(error);
            }
        })();
    },



    // when receiving interaction
    onSlash: function (interaction) {

        var channelsList = require("../config/public/channelsChat.json");
        try {
            // send message
            if (interaction.commandName === 'send') {
                const msgtosend = interaction.options.getString('message') ?? 'No message provided';
                if (reqFcts.sendMsg(interaction.user.username, msgtosend) == 1)
                    return interaction.reply({ content: "Le message \n```" + msgtosend + "```\nn'a pas pu être envoyé, ERREUR : " + res.statusCode, ephemeral: true });
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
                saver.updatecfg(`./config/public/channelsChat.json`, channelsList)
                interaction.reply({ content: `Le salon <#${interaction.channel.id}> a bien été ajouté`, ephemeral: true });
            }

            else if (interaction.commandName === 'remchannel') {
                if (interaction.user.id != "444579657279602699" && interaction.member.permissions.has(Permissions.FLAGS.MANAGE_MESSAGES))
                    return interaction.reply({ content: "Tu n'es pas modérateur sur ce serveur !", ephemeral: true });

                channelsList[interaction.channel.id].sendMsgInIt = 0;
                saver.updatecfg(`./config/public/channelsChat.json`, channelsList)
                interaction.reply({ content: `Le salon <#${interaction.channel.id}> a bien été supprimé de la liste des mises à jour`, ephemeral: true });
            }
        } catch (error) {
            interaction.reply({ content: "Une erreur est survenue lol", ephemeral: true });
        }
    }
}