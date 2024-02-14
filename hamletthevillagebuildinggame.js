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

const Buildings = Object.freeze([
    "church", "market", "shrine", "farm", "trade-post", "master-stonemason",
    "warehouse", "woodcutter", "flour-mill", "small-woodland",
    "large-woodland", "windmill", "tavern", "dairy-farm", "outpost-1",
    "outpost-2", "stables", "cow-conservatory", "sawmill", "straight-barn",
    "curved-barn", "quarry", "forest-pond", "mountain-pond", "farrier",
    "small-mountain-range", "large-mountain-range", "square", "monument",
    "stonemason", "lumber-mill", "town-hall"
]);

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
    const id = `hamlet-${Buildings[parseInt(building.id)]}`;
    const properties = {
        cx: parseInt(building.x) - parseInt(building.z),
        cy: parseInt(building.y),
        orientation: parseInt(building.orientation),
        sign: parseInt(building.orientation) & 0b1
    }
    const style = Object.keys(properties)
        .map(name => `--${name}: ${properties[name]}`)
        .join("; ");
    return `<div id="${id}" style="${style}" class="hamlet-building" data-building="${building.id}"
            data-x="${building.x}" data-y="${building.y}" data-z="${building.z}">
        ${spaces.map(createSpace).join("")}
    </div>`;
}

const buildingShapes = Object.freeze({
    church: {
        xSpaces: 5,
        ySpaces: 4,
        spaceOffset: 1.5,
        clip: [0, 0.5, 0.1, 0.25, 0.3, 0.25, 0.4, 0, 0.6, 0, 0.7, 0.25, 0.9, 0.25, 1, 0.5, 0.9, 0.75, 0.7, 0.75, 0.6, 1, 0.4, 1, 0.3, 0.75, 0.1, 0.75]
    },
    largeTriangle: {
        xSpaces: 3,
        ySpaces: 3,
        spaceOffset: 1,
        clip: [0, 1, 0.5, 0, 1, 1]
    },
    smallTriangle: {
        xSpaces: 2,
        ySpaces: 2,
        spaceOffset: 0.5,
        clip: [0, 1, 0.5, 0, 1, 1]
    },
    diamond: {
        xSpaces: 2,
        ySpaces: 4,
        spaceOffset: 0.5,
        clip: [0, 0.5, 0.5, 0, 1, 0.5, 0.5, 1]
    },
    cutDiamond: {
        xSpaces: 2,
        ySpaces: 3,
        spaceOffset: 0.5,
        clip: [0, 0.667, 0.5, 0, 1, 0.667, 0.75, 1, 0.25, 1]
    },
    flask: {
        xSpaces: 2,
        ySpaces: 3,
        spaceOffset: 0.5,
        clip: [0, 0.667, 0.5, 0, 1, 0, 0.75, 0.333, 1, 0.667, 0.75, 1, 0.25, 1]
    },
    flag: {
        xSpaces: 2.5,
        ySpaces: 4,
        spaceOffset: 1,
        clip: [0, 0.75, 0.6, 0, 1, 0.5, 0.6, 0.5, 0.8, 0.75, 0.6, 1, 0.2, 1]
    },
    hex: {
        xSpaces: 2,
        ySpaces: 2,
        spaceOffset: 0,
        clip: [0, 0.5, 0.25, 0, 0.75, 0, 1, 0.50, 0.75, 1, 0.25, 1]
    },
    hexHalf: {
        xSpaces: 2,
        ySpaces: 3,
        spaceOffset: 0,
        clip: [0, 0.333, 0.25, 0, 0.75, 0, 1, 0.333, 0.75, 0.667, 1, 1, 0, 1, 0.25, 0.667]
    },
    doubleHex: {
        xSpaces: 2,
        ySpaces: 4,
        spaceOffset: 0,
        clip: [0, 0.25, 0.25, 0, 0.75, 0, 1, 0.25, 0.75, 0.5, 1, 0.75, 0.75, 1, 0.25, 1, 0, 0.75, 0.25, 0.50]
    }
});

function clipBuilding(building) {
    const style = getComputedStyle(building);
    const shape = buildingShapes[style.getPropertyValue("--shape")];
    building.style.setProperty("--x-spaces", shape.xSpaces.toString());
    building.style.setProperty("--y-spaces", shape.ySpaces.toString());
    building.style.setProperty("--space-offset", shape.spaceOffset.toString());
    const width = shape.xSpaces * parseInt(style.getPropertyValue("--space-width"));
    const height = shape.ySpaces * parseInt(style.getPropertyValue("--space-height"));
    const clip = shape.clip;

    const steps = [];
    for (let i = 0; i < clip.length; i += 2) {
        steps.push(`${clip[i] * width},${clip[i + 1] * height}`);
    }
    building.style.setProperty("--clip", `"M${steps.join(" ")}Z"`);
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

        this.board = document.getElementById("hamlet-board");

        for (const building of data.buildings) {
            const element= createElement(this.board,
                createBuilding(building, []));

            clipBuilding(element);
        }

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

                    const building = {
                        id: state.args.buildingId,
                        x: 0,
                        y: 0,
                        z: 0,
                        orientation: 0
                    };

                    const spaces = [];
                    while (state.args.spaces.length >= 6) {
                        const [x, y, z, edge_x, edge_y, edge_z] = state.args.spaces.splice(0, 6);
                        const space = {
                            x, y, z, edge_x, edge_y, edge_z, building_id: building.id};
                        spaces.push(space);
                    }

                    this.spaces = spaces;

                    this.currentSpace = {x: 0, y: 0, z: 0};
                    this.currentOrientation = 0;
                    this.currentBuilding = createElement(this.board,
                        createBuilding(building, this.spaces));
                    clipBuilding(this.currentBuilding);
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
                        const orientation = this.currentOrientation >= 0 ?
                            this.currentOrientation % 6 :
                            (this.currentOrientation % 6 + 6) % 6;
                        this.request("build", {orientation, ...this.currentSpace});
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
            this.currentOrientation = this.currentOrientation + steps;
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
