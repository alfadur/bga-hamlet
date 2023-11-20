/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * HamletTheVillageBuildingGame implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

function createElement(parent, html) {
    const element = document.createElement("div");
    parent.appendChild(element);
    element.outerHTML = html;
    return parent.lastChild;
}

function createSpace({x, y, z, building_id: building}) {
    const orientation = parseInt(x) + parseInt(y) + parseInt(z);
    const style=`--cx: ${parseInt(z) - parseInt(x)}; --cy: ${y}`;
    return `<div class="hamlet-board-space" 
        data-x="${x}" data-y="${y}" data-z="${z}"    
        data-orientation="${orientation}"
        data-building="${building}"
        style="${style}">
    </div>`;
}

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter"
], (dojo, declare) => declare("bgagame.hamletthevillagebuildinggame", ebg.core.gamegui, {
    constructor() {
        console.log("hamletthevillagebuildinggame constructor");
    },

    setup(data) {
        console.log("Starting game setup");

        for (const player_id of Object.keys(data.players)) {
            const player = data.players[player_id];
        }

        const board =document.getElementById("hamlet-board");
        for (const space of data.board) {
            createElement(board, createSpace(space));
        }

        const bounds = data.board.reduce(
            (bounds, space) => ({
                minX: Math.min(bounds.minX, parseInt(space.z) - parseInt(space.x)),
                minY: Math.min(bounds.minY, parseInt(space.y)),
                maxX: Math.max(bounds.maxX, parseInt(space.z) - parseInt(space.x)),
                maxY: Math.max(bounds.maxY, parseInt(space.y)),
            }), {
                minX: Number.MAX_SAFE_INTEGER,
                minY: Number.MAX_SAFE_INTEGER,
                maxX: Number.MIN_SAFE_INTEGER,
                maxY: Number.MIN_SAFE_INTEGER
            }
        )

        board.style.setProperty("--minCx", bounds.minX);
        board.style.setProperty("--minCy", bounds.minY);
        board.style.setProperty("--maxCx", bounds.maxX);
        board.style.setProperty("--maxCy", bounds.maxY);

        this.setupNotifications();

        console.log("Ending game setup");
    },

    onEnteringState(stateName, args) {
        console.log(`Entering state: ${stateName}`);

        switch (stateName) {

        }
    },

    onLeavingState(stateName) {
        console.log(`Leaving state: ${stateName}`);

        switch (stateName) {

        }
    },

    onUpdateActionButtons(stateName, args) {
        console.log(`onUpdateActionButtons: ${stateName}`);

        if (this.isCurrentPlayerActive()) {
            switch (stateName) {
            }
        }
    },

    setupNotifications() {
        console.log("notifications subscriptions setup");
    }
}));
