'use strict';

// ============================================================
// STATE
// ============================================================
const state = {
  screen: 'people',
  contacts: [],
  favorites: [],   // [{id, contact_id, contact, order}]
  loading: false,
  installPrompt: null,
  sheetContactId: null,
};

// ============================================================
// AVATAR COLOURS
// ============================================================
const AVATAR_COLORS = [
  '#0d9488', '#2563eb', '#b45309', '#059669',
  '#7c3aed', '#0891b2', '#d97706', '#0284c7',
  '#db2777', '#65a30d',
];

function getAvatarColor(name) {
  if (!name) return AVATAR_COLORS[0];
  let h = 0;
  for (let i = 0; i < name.length; i++) h = name.charCodeAt(i) + ((h << 5) - h);
  return AVATAR_COLORS[Math.abs(h) % AVATAR_COLORS.length];
}

// Hex colour → rgba for glow
function hexToRgba(hex, alpha) {
  const r = parseInt(hex.slice(1,3),16);
  const g = parseInt(hex.slice(3,5),16);
  const b = parseInt(hex.slice(5,7),16);
  return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
}

function getInitials(name) {
  if (!name) return '?';
  const parts = name.trim().split(/\s+/);
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
  return name.slice(0, 2).toUpperCase();
}

function firstName(name) {
  return (name || '').trim().split(/\s+/)[0];
}

// ============================================================
// XSS SAFETY
// ============================================================
function esc(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
    .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

// ============================================================
// API
// ============================================================
async function apiRequest(method, path, body) {
  const opts = { method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' } };
  if (body) opts.body = JSON.stringify(body);
  const res = await fetch('/api' + path, opts);
  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.message || 'HTTP ' + res.status);
  }
  if (res.status === 204) return null;
  return res.json();
}

async function loadContacts() {
  state.loading = true;
  try {
    const data = await apiRequest('GET', '/contacts');
    state.contacts = (data.contacts || []).sort((a, b) => a.name.localeCompare(b.name));
    state.favorites = data.favorites || [];
  } catch (err) {
    console.error(err);
    showToast('Could not load contacts \u2014 check connection');
  } finally {
    state.loading = false;
    renderPeople();
    renderManageList();
    renderMessages();
    updateContactCount();
  }
}

async function createContact(data) {
  const contact = await apiRequest('POST', '/contacts', data);
  state.contacts.push(contact);
  state.contacts.sort((a, b) => a.name.localeCompare(b.name));
  return contact;
}

async function deleteContactById(id) {
  await apiRequest('DELETE', '/contacts/' + id);
  state.contacts  = state.contacts.filter(c => c.id !== id);
  state.favorites = state.favorites.filter(f => f.contact_id !== id);
}

async function addFavourite(contactId) {
  if (state.favorites.length >= 4) { showToast('My Favourites is full (4 max)'); return; }
  const fav = await apiRequest('POST', '/contacts/' + contactId + '/favorite');
  // Ensure contact is populated on the fav object
  if (!fav.contact) fav.contact = state.contacts.find(c => c.id === contactId);
  state.favorites.push(fav);
}

async function removeFavourite(contactId) {
  await apiRequest('DELETE', '/contacts/' + contactId + '/favorite');
  state.favorites = state.favorites.filter(f => f.contact_id !== contactId);
}

async function refreshData() {
  showToast('Refreshing\u2026');
  await loadContacts();
  showToast('Contacts refreshed');
}

// ============================================================
// NAVIGATION
// ============================================================
function navigate(screen) {
  const prev = document.getElementById('screen-' + state.screen);
  const next = document.getElementById('screen-' + screen);
  if (!next) return;

  if (prev) prev.classList.remove('active');
  next.classList.add('active');
  state.screen = screen;

  document.querySelectorAll('.nav-btn').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.screen === screen);
  });

  if (screen === 'people')   renderPeople();
  if (screen === 'messages') renderMessages();
  if (screen === 'settings') { updateContactCount(); loadSosInputs(); renderManageList(); }
}

// ============================================================
// CLOCK
// ============================================================
function updateClock() {
  const now  = new Date();
  const timeEl = document.getElementById('header-time');
  const dateEl = document.getElementById('header-date');
  if (!timeEl) return;
  const h = String(now.getHours()).padStart(2,'0');
  const m = String(now.getMinutes()).padStart(2,'0');
  timeEl.textContent = h + ':' + m;
  const DAYS   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
  const MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  dateEl.textContent = DAYS[now.getDay()] + ' ' + now.getDate() + ' ' + MONTHS[now.getMonth()];
}

// ============================================================
// RENDER: YOUR PEOPLE (4-slot hero grid)
// ============================================================
function renderPeople() {
  const grid = document.getElementById('hero-grid');
  if (!grid) return;

  let html = '';

  for (let i = 0; i < 4; i++) {
    const fav = state.favorites[i];

    if (fav && fav.contact) {
      const contact = fav.contact;
      const color   = getAvatarColor(contact.name);
      const glow    = hexToRgba(color, 0.25);
      const initials = getInitials(contact.name);
      const id = fav.contact_id;

      html += '<div class="hero-card" style="--glow-color:' + glow + '" onclick="heroTap(' + id + ')">'
        + '<button class="hero-more-btn" onclick="event.stopPropagation();showActionSheet(' + id + ')" title="More options">'
        + '<span class="material-icons-round">more_horiz</span></button>'
        + '<div class="hero-avatar" style="background:' + color + ';box-shadow:0 4px 18px ' + glow + '">'
        + esc(initials) + '</div>'
        + '<div class="hero-name">' + esc(firstName(contact.name)) + '</div>'
        + '<div class="hero-phone">' + esc(contact.phone || '') + '</div>'
        + '<div class="hero-call-hint"><span class="material-icons-round">call</span>Tap to call</div>'
        + '</div>';

    } else {
      html += '<div class="hero-empty" onclick="navigate(\'settings\')">'
        + '<div class="hero-empty-icon"><span class="material-icons-round">person_add</span></div>'
        + '<div class="hero-empty-label">Add a Person</div>'
        + '</div>';
    }
  }

  grid.innerHTML = html;

  // SOS card — always visible
  const sosNumber = localStorage.getItem('sos-number');
  const sosName   = localStorage.getItem('sos-name') || 'Emergency contact';
  const sosCard   = document.getElementById('sos-card');
  const sosSub    = document.getElementById('sos-subtitle');
  if (sosCard) {
    sosCard.classList.remove('hidden');
    if (sosSub) {
      sosSub.textContent = sosNumber
        ? sosName + ' \u2014 ' + sosNumber
        : 'Tap to set up your emergency contact';
    }
  }
}

// Tap a hero card = straight call
function heroTap(contactId) {
  const fav = state.favorites.find(f => f.contact_id === contactId);
  const contact = fav ? fav.contact : state.contacts.find(c => c.id === contactId);
  if (!contact || !contact.phone) { showToast('No phone number'); return; }
  window.location.href = 'tel:' + contact.phone;
}

// ============================================================
// ACTION SHEET (··· button)
// ============================================================
function showActionSheet(contactId) {
  const fav = state.favorites.find(f => f.contact_id === contactId);
  const contact = fav ? fav.contact : state.contacts.find(c => c.id === contactId);
  if (!contact) return;

  state.sheetContactId = contactId;
  document.getElementById('sheet-name').textContent  = contact.name;
  document.getElementById('sheet-phone').textContent = contact.phone || '';
  document.getElementById('action-sheet-overlay').classList.remove('hidden');
}

function hideActionSheet() {
  document.getElementById('action-sheet-overlay').classList.add('hidden');
  state.sheetContactId = null;
}

function sheetAction(action) {
  const id = state.sheetContactId;
  hideActionSheet();
  if (id === null) return;

  const fav = state.favorites.find(f => f.contact_id === id);
  const contact = fav ? fav.contact : state.contacts.find(c => c.id === id);
  if (!contact || !contact.phone) { showToast('No phone number'); return; }

  switch (action) {
    case 'call':
      window.location.href = 'tel:' + contact.phone;
      break;
    case 'whatsapp': {
      const digits = contact.phone.replace(/\D/g, '');
      window.open('https://wa.me/' + digits, '_blank');
      break;
    }
  }
}

// ============================================================
// RENDER: MESSAGES
// ============================================================
function renderMessages() {
  const list = document.getElementById('messages-list');
  if (!list) return;

  const withPhone = state.contacts.filter(c => c.phone);
  if (withPhone.length === 0) {
    list.innerHTML = '<div class="empty-state"><span class="material-icons-round">chat_bubble_outline</span>'
      + '<p>No contacts with phone numbers yet. Add contacts in Settings.</p></div>';
    return;
  }

  list.innerHTML = withPhone.map(contact => {
    const color    = getAvatarColor(contact.name);
    const initials = getInitials(contact.name);
    return '<div class="message-row">'
      + '<div class="message-avatar" style="background:' + color + '">' + esc(initials) + '</div>'
      + '<div class="message-info">'
      + '<div class="message-name">' + esc(contact.name) + '</div>'
      + '<div class="message-phone">' + esc(contact.phone) + '</div>'
      + '</div>'
      + '<button class="message-sms-btn" onclick="whatsappContact(\'' + esc(contact.phone) + '\')">'
      + '<span class="material-icons-round">chat</span>WhatsApp</button>'
      + '</div>';
  }).join('');
}

function whatsappContact(phone) {
  const digits = phone.replace(/\D/g, '');
  window.open('https://wa.me/' + digits, '_blank');
}

// ============================================================
// RENDER: MANAGE LIST (in Settings)
// ============================================================
function renderManageList() {
  const list = document.getElementById('manage-list');
  if (!list) return;

  if (state.loading) {
    list.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><p>Loading\u2026</p></div>';
    return;
  }

  if (state.contacts.length === 0) {
    list.innerHTML = '<div class="empty-state"><span class="material-icons-round">people_outline</span>'
      + '<p>No contacts yet. Import from your phone or add manually.</p></div>';
    return;
  }

  const favIds = new Set(state.favorites.map(f => f.contact_id));

  list.innerHTML = state.contacts.map(contact => {
    const color    = getAvatarColor(contact.name);
    const initials = getInitials(contact.name);
    const isFav    = favIds.has(contact.id);
    const id       = contact.id;

    return '<div class="manage-row">'
      + '<div class="manage-avatar" style="background:' + color + '">' + esc(initials) + '</div>'
      + '<div class="manage-info">'
      + '<div class="manage-name">' + esc(contact.name) + '</div>'
      + '<div class="manage-phone">' + esc(contact.phone || contact.email || '') + '</div>'
      + '</div>'
      + '<div class="manage-actions">'
      + '<button class="manage-star-btn' + (isFav ? ' active' : '') + '" onclick="toggleFavourite(' + id + ',' + isFav + ')" title="' + (isFav ? 'Remove from My Favourites' : 'Add to My Favourites') + '">'
      + '<span class="material-icons-round">' + (isFav ? 'star' : 'star_border') + '</span></button>'
      + '<button class="manage-delete-btn" onclick="confirmDelete(' + id + ')" title="Delete contact">'
      + '<span class="material-icons-round">delete</span></button>'
      + '</div>'
      + '</div>';
  }).join('');
}

function updateContactCount() {
  const el = document.getElementById('contact-count-label');
  if (!el) return;
  const n = state.contacts.length;
  const f = state.favorites.length;
  el.textContent = n + ' contact' + (n === 1 ? '' : 's') + ', ' + f + ' of 4 favourite slots used';
}

// ============================================================
// TOGGLE FAVOURITE
// ============================================================
async function toggleFavourite(contactId, isFav) {
  try {
    if (isFav) {
      await removeFavourite(contactId);
      showToast('Removed from My Favourites');
    } else {
      await addFavourite(contactId);
      showToast('Added to My Favourites');
    }
    renderPeople();
    renderManageList();
    updateContactCount();
  } catch (err) {
    showToast('Could not update: ' + err.message);
  }
}

// ============================================================
// DELETE CONTACT
// ============================================================
function confirmDelete(contactId) {
  const contact = state.contacts.find(c => c.id === contactId);
  if (!contact) return;
  if (!confirm('Delete ' + contact.name + '?')) return;

  deleteContactById(contactId)
    .then(function() {
      renderPeople();
      renderManageList();
      renderMessages();
      updateContactCount();
      showToast('Contact deleted');
    })
    .catch(function() { showToast('Failed to delete contact'); });
}

// ============================================================
// ADD CONTACT MANUALLY
// ============================================================
function showAddContactModal() {
  document.getElementById('add-contact-modal').classList.remove('hidden');
  setTimeout(function() { document.getElementById('contact-name-input').focus(); }, 150);
}

function hideAddContactModal() {
  document.getElementById('add-contact-modal').classList.add('hidden');
  document.getElementById('add-contact-form').reset();
}

async function submitAddContact(e) {
  e.preventDefault();
  const name  = document.getElementById('contact-name-input').value.trim();
  const phone = document.getElementById('contact-phone-input').value.trim();
  if (!name) return;

  const btn = document.getElementById('add-contact-submit');
  btn.disabled = true;
  btn.textContent = 'Adding\u2026';

  try {
    await createContact({ name, phone: phone || null });
    hideAddContactModal();
    renderManageList();
    renderMessages();
    updateContactCount();
    showToast(name + ' added');
  } catch (err) {
    showToast('Failed: ' + err.message);
  } finally {
    btn.disabled = false;
    btn.textContent = 'Add Contact';
  }
}

// ============================================================
// IMPORT FROM PHONE (Contact Picker API)
// ============================================================
async function importFromPhone() {
  if (!('contacts' in navigator) || !('ContactsManager' in window)) {
    showToast('Import not supported on this browser \u2014 use Chrome on Android');
    return;
  }

  try {
    const picked = await navigator.contacts.select(['name', 'tel'], { multiple: true });
    if (!picked.length) { showToast('No contacts selected'); return; }

    let added = 0;
    let skipped = 0;

    for (const c of picked) {
      const name  = (c.name && c.name[0]) ? c.name[0].trim() : null;
      const phone = (c.tel  && c.tel[0])  ? c.tel[0].trim()  : null;
      if (!name) { skipped++; continue; }

      // Avoid duplicates (simple name match)
      const exists = state.contacts.some(existing => existing.name.toLowerCase() === name.toLowerCase());
      if (exists) { skipped++; continue; }

      try {
        await createContact({ name, phone: phone || null });
        added++;
      } catch (err) {
        skipped++;
      }
    }

    renderManageList();
    renderMessages();
    updateContactCount();

    if (added > 0) {
      showToast(added + ' contact' + (added === 1 ? '' : 's') + ' imported' + (skipped ? ', ' + skipped + ' skipped' : ''));
    } else {
      showToast('No new contacts added' + (skipped ? ' (' + skipped + ' already exist)' : ''));
    }
  } catch (err) {
    // User cancelled or permission denied
    showToast('Import cancelled');
  }
}

// ============================================================
// SOS
// ============================================================
function sosTap() {
  const num = localStorage.getItem('sos-number');
  if (num) {
    window.location.href = 'tel:' + num;
  } else {
    navigate('settings');
    showToast('Set your emergency number in Settings');
  }
}

function saveSosField(key, value) {
  const trimmed = value.trim();
  if (trimmed) { localStorage.setItem(key, trimmed); }
  else         { localStorage.removeItem(key); }
  renderPeople();
}

function loadSosInputs() {
  const n = document.getElementById('sos-number-input');
  const l = document.getElementById('sos-name-input');
  if (n) n.value = localStorage.getItem('sos-number') || '';
  if (l) l.value = localStorage.getItem('sos-name')   || '';
}

// ============================================================
// KEYPAD
// ============================================================
let keypadDigits = '';

function renderKeypad() {
  const el = document.getElementById('keypad-digits');
  if (el) el.textContent = keypadDigits || '\u00a0';
}

function keypadPress(d) { keypadDigits += d; renderKeypad(); }
function keypadDelete()  { keypadDigits = keypadDigits.slice(0,-1); renderKeypad(); }
function keypadClear()   { keypadDigits = ''; renderKeypad(); }
function keypadCall()    { keypadDigits ? window.location.href = 'tel:' + keypadDigits : showToast('Enter a number'); }
function keypadWhatsApp() { keypadDigits ? window.open('https://wa.me/' + keypadDigits.replace(/\D/g,''), '_blank') : showToast('Enter a number'); }

function initKeypadLongPress() {
  const btn = document.getElementById('keypad-delete-btn');
  if (!btn) return;
  let timer = null;
  const start = function() { timer = setTimeout(keypadClear, 700); };
  const stop  = function() { clearTimeout(timer); };
  btn.addEventListener('mousedown',  start);
  btn.addEventListener('touchstart', start, { passive: true });
  btn.addEventListener('mouseup',    stop);
  btn.addEventListener('mouseleave', stop);
  btn.addEventListener('touchend',   stop);
}

// ============================================================
// PWA INSTALL
// ============================================================
function showInstallPrompt() {
  if (state.installPrompt) {
    state.installPrompt.prompt();
    state.installPrompt.userChoice.then(function() {
      state.installPrompt = null;
      document.getElementById('install-banner').classList.add('hidden');
    });
  } else {
    showToast('Browser menu \u2192 Add to Home Screen');
  }
}

// ============================================================
// TOAST
// ============================================================
let toastTimer = null;

function showToast(msg, ms) {
  if (!ms) ms = 2600;
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.remove('hidden');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(function() { t.classList.add('hidden'); }, ms);
}

// ============================================================
// UTILITY
// ============================================================
function overlayClose(e, fn) {
  if (e.target === e.currentTarget) fn();
}

// ============================================================
// INIT
// ============================================================
async function init() {
  updateClock();
  setInterval(updateClock, 30000);

  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').catch(function(e) { console.warn('SW:', e); });
  }

  window.addEventListener('beforeinstallprompt', function(e) {
    e.preventDefault();
    state.installPrompt = e;
    if (!localStorage.getItem('install-dismissed')) {
      document.getElementById('install-banner').classList.remove('hidden');
    }
  });

  document.getElementById('install-btn').addEventListener('click', showInstallPrompt);
  document.getElementById('dismiss-install').addEventListener('click', function() {
    document.getElementById('install-banner').classList.add('hidden');
    localStorage.setItem('install-dismissed', '1');
  });

  document.getElementById('add-contact-modal').addEventListener('click', function(e) {
    if (e.target === this) hideAddContactModal();
  });

  initKeypadLongPress();
  await loadContacts();
}

document.addEventListener('DOMContentLoaded', init);
