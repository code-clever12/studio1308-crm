import './bootstrap';

import Alpine from 'alpinejs';
import walkInBooking from './walk-in-booking';

window.Alpine = Alpine;

Alpine.data('walkInBooking', walkInBooking);

Alpine.start();
