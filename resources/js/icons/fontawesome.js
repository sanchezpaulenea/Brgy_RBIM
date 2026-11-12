import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

/**
 * Font Awesome Pro "house-building" (classic solid) — path matched to the FA glyph.
 * Note: StatTileIcon renders this with a knockout mask for the house/building gap;
 * keep this definition for any <font-awesome-icon icon="fa-solid fa-house-building" /> usage.
 */
const faHouseBuilding = {
    prefix: 'fas',
    iconName: 'house-building',
    icon: [
        640,
        512,
        [],
        'e047',
        'M352 8h224c28 0 48 20 48 48v440H352V8zm100 80h72v52h-72V88zm0 100h72v52h-72v-52zm0 100h72v52h-72v-52zM24 248 208 52c16-15 40-15 56 0l156 148c14 13 5 36-16 36h-40v252H80V284H40c-21 0-30-23-16-36zM168 300h96v88h-96v-88z',
    ],
};

library.add(faHouseBuilding);

export { FontAwesomeIcon, faHouseBuilding };
