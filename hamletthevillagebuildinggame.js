/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * HamletTheVillageBuildingGame implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 */

const gameName = "hamletthevillagebuildinggame";

const Edge = Object.freeze({
    none: 0,
    road: 1,
    forest: 2,
    mountain: 3
});

function createElement(parent, html) {
    const element = document.createElement("div");
    parent.appendChild(element);
    element.outerHTML = html;
    return parent.lastChild;
}

function createSpace({x, y, z, edge_x, edge_y, edge_z, building_id: building}) {
    const orientation = parseInt(x) + parseInt(y) + parseInt(z);
    const style=`--cx: ${parseInt(x) - parseInt(z)}; --cy: ${y}`;
    return `<div class="hamlet-board-space" 
            data-x="${x}" data-y="${y}" data-z="${z}"    
            data-orientation="${orientation}"
            data-building="${building}"
            style="${style}">
        <div class="hamlet-edge hamlet-edge-x" data-edge="${edge_x}"></div>
        <div class="hamlet-edge hamlet-edge-y" data-edge="${edge_y}"></div>
        <div class="hamlet-edge hamlet-edge-z" data-edge="${edge_z}"></div>
    </div>`;
}

function createBuilding(building, spaces) {
    return `<div class="hamlet-building" data-building="${building}">
        ${spaces.map(createSpace).join("")}
    </div>`;
}

function getBounds(spaces) {
    return spaces.reduce((bounds, space) => ({
        minX: Math.min(bounds.minX, parseInt(space.x) - parseInt(space.z)),
        minY: Math.min(bounds.minY, parseInt(space.y)),
        maxX: Math.max(bounds.maxX, parseInt(space.x) - parseInt(space.z)),
        maxY: Math.max(bounds.maxY, parseInt(space.y)),
    }), {
        minX: Number.MAX_SAFE_INTEGER,
        minY: Number.MAX_SAFE_INTEGER,
        maxX: Number.MIN_SAFE_INTEGER,
        maxY: Number.MIN_SAFE_INTEGER
    });
}

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter"
], (dojo, declare) => declare(`bgagame.${gameName}`, ebg.core.gamegui, {
    constructor() {
        console.log(`${gameName} constructor`);
    },

    setup(data) {
        console.log("Starting game setup");

        for (const player_id of Object.keys(data.players)) {
            const player = data.players[player_id];
        }

        this.board =document.getElementById("hamlet-board");
        for (const space of data.board) {
            createElement(this.board, createSpace(space));
        }

        const bounds = getBounds(data.board)

        this.board.style.setProperty("--minCx", bounds.minX);
        this.board.style.setProperty("--minCy", bounds.minY);
        this.board.style.setProperty("--maxCx", bounds.maxX);
        this.board.style.setProperty("--maxCy", bounds.maxY);

        this.setupNotifications();

        console.log("Ending game setup");
    },

    onEnteringState(stateName, state) {
        console.log(`Entering state: ${stateName}`);

        if (this.isCurrentPlayerActive()) {
            switch (stateName) {
                case 'placeBuilding': {
                    console.log(state.args);
                    const building = parseInt(state.args.building);

                    const spaces = [];
                    while (state.args.spaces.length >= 6) {
                        const [x, y, z, edge_x, edge_y, edge_z] = state.args.spaces.splice(0, 6);
                        const space = {
                            x, y, z, edge_x, edge_y, edge_z, building_id: building};
                        spaces.push(space);
                    }

                    this.spaces = spaces;

                    this.currentSpace = {x: 0, y: 0, z: 0};
                    this.currentOrientation = 0;
                    this.currentBuilding = createElement(this.board,
                        createBuilding(building, this.spaces));
                    break;
                }
            }
        }
    },

    onLeavingState(stateName) {
        console.log(`Leaving state: ${stateName}`);

        if (this.isCurrentPlayerActive()) {
            switch (stateName) {
                case "placeBuilding": {
                    this.currentBuilding.remove();
                    delete this.currentBuilding;
                    break;
                }
            }
        }
    },

    onUpdateActionButtons(stateName, args) {
        console.log(`onUpdateActionButtons: ${stateName}`);

        if (this.isCurrentPlayerActive()) {
            switch (stateName) {
                case "placeBuilding": {
                    this.addActionButton('hamlet-build', _("Build"), () => {
                        this.request("build", {
                            orientation: this.currentOrientation,
                            ...this.currentSpace
                        });
                    });

                    const movement = [
                        {id: "left", icon: "⬅", shift: [-1, 0, 1]},
                        {id: "right", icon: "➡", shift: [1, 0, -1]},
                        {id: "up-left", icon: "↖", shift: [0, -1, 1]},
                        {id: "up-right", icon: "↗", shift: [1, -1, 0]},
                        {id: "down-left", icon: "↙", shift: [-1, 1, 0]},
                        {id: "down-right", icon: "↘", shift: [0, 1, -1]},
                    ];

                    for (const {id, icon, shift} of movement) {
                        this.addActionButton(`hamlet-${id}`, icon, () => {
                            this.moveBuilding(...shift);
                        });
                    }

                    this.addActionButton("hamlet-rotate", "🔁", () => {
                        this.rotateBuilding(1);
                    });

                    this.addActionButton("hamlet-unrotate", "🔄", () => {
                        this.rotateBuilding(-1);
                    });

                    break;
                }
            }
        }
    },

    rotateBuilding(steps) {
        const building = this.currentBuilding;
        if (building) {
            const sum = this.currentOrientation & 0b1;
            const shift = 1 - sum * 2;

            /*const bounds = getBounds(this.spaces);
            const tx = (bounds.minX + bounds.maxX) >> 1;
            const y = (bounds.minY + bounds.maxY) >> 1;

            const z = (sum - (tx + y)) >> 1
            const x = tx + z;

            this.currentSpace = {
                x: this.currentSpace.x + x - z,
                y: this.currentSpace.y + y - x,
                z: this.currentSpace.z + z - y
            };*/

            this.currentSpace.x += shift;
            this.currentOrientation =
                (this.currentOrientation + steps + 6) % 6;
            const cx = this.currentSpace.x - this.currentSpace.z;
            const cy = this.currentSpace.y;

            building.style.setProperty("--cx", cx.toString());
            building.style.setProperty("--cy", cy.toString());
            building.style.setProperty("--orientation",
                this.currentOrientation.toString());
            building.style.setProperty("--sign",
                (this.currentOrientation & 0b1).toString());
        }
    },

    moveBuilding(dx, dy, dz) {
        console.log("Move building by", {dx, dy, dz});
        const building = this.currentBuilding;
        if (building) {
            this.currentSpace.x += dx;
            this.currentSpace.y += dy;
            this.currentSpace.z += dz;
            const x = this.currentSpace.x - this.currentSpace.z;
            const y = this.currentSpace.y;
            building.style.setProperty("--cx", x.toString());
            building.style.setProperty("--cy", y.toString());
        }
    },

    request(action, args, onSuccess) {
        this.ajaxcall(`/${gameName}/${gameName}/${action}.html`, {
            lock: true,
            ...args
        }, () => {
            if (typeof onSuccess === "function") {
                onSuccess();
            }
        });
    },

    setupNotifications() {
        console.log("notifications subscriptions setup");
    }
}));
