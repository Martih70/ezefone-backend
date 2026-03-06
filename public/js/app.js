'use strict';

// ============================================================
// STATE
// ============================================================
const state = {
  screen: 'contacts',
  contacts: [],
  loading: false,
  manageQuery: '',
  installPrompt: null,
};

// ============================================================
// AVATAR COLOURS
// ============================================================
const AVATAR_COLORS = [
  '#0d9488', '#2563eb', '#b45309', '#059669',
  '#7c3aed', '#dc2626', '#d97706', '#0284c7',
  '#db2777', '#ea580c',
];

function getAvatarColor(name) {
  if (!name) return AVATAR_COLORS[0];
  let hash = 0;
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash);
  }
  return AVATAR_COLORS[Math.abs(hash) % AVATAR_COLORS.length];
}

function getInitials(name) {
  if (!name) return '?';
  const parts = name.trim().split(/\s+/);
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
  return name.slice(0, 2).toUpperCase();
}

// ============================================================
// HTML ESCAPING (XSS safety)
// ============================================================
function esc(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

// ============================================================
// API
// ============================================================
async function apiRequest(method, path, body) {
  const opts = {
    method,
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  };
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
  } catch (err) {
    console.error('Failed to load contacts:', err);
    showToast('Could not load contacts \u2014 check connection');
  } finally {
    state.loading = false;
    renderContacts();
    renderManage();
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
  state.contacts = state.contacts.filter(c => c.id !== id);
}

async function refreshData() {
  showToast('Refreshing\u2026');
  await loadContacts();
  showToast('Contacts refreshed');
}

// ============================================================
// NAVIGATION
// ============================================================
// Screens that are not nav tabs (managed separately)
const NON_TAB_SCREENS = ['manage'];

function navigate(screen) {
  const prev = document.getElementById('screen-' + state.screen);
  const next = document.getElementById('screen-' + screen);
  if (!next) return;

  if (prev) prev.classList.remove('active');
  next.classList.add('active');
  state.screen = screen;

  // Update bottom nav highlight (only for the 4 tab screens)
  if (!NON_TAB_SCREENS.includes(screen)) {
    document.querySelectorAll('.nav-btn').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.screen === screen);
    });
  }

  // FAB visibility: show on contacts tab only
  const fab = document.getElementById('fab-add');
  if (fab) fab.style.display = (screen === 'contacts') ? 'flex' : 'none';

  // Render the screen
  switch (screen) {
    case 'contacts': renderContacts(); break;
    case 'messages': renderMessages(); break;
    case 'settings': updateContactCount(); loadSosInputs(); break;
    case 'manage':   renderManage(); break;
    case 'keypad':   renderKeypad(); break;
  }
}

// ============================================================
// HEADER: CLOCK
// ============================================================
function updateClock() {
  const now = new Date();
  const timeEl = document.getElementById('header-time');
  const dateEl = document.getElementById('header-date');
  if (!timeEl || !dateEl) return;

  const h = now.getHours().toString().padStart(2, '0');
  const m = now.getMinutes().toString().padStart(2, '0');
  timeEl.textContent = h + ':' + m;

  const days   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
  const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  dateEl.textContent = days[now.getDay()] + ' ' + now.getDate() + ' ' + months[now.getMonth()];
}

// ============================================================
// RENDER: CONTACTS (2-col card grid)
// ============================================================
function renderContacts() {
  const grid = document.getElementById('contacts-grid');
  if (!grid) return;

  if (state.loading) {
    grid.innerHTML = '<div class="loading-spinner" style="grid-column:1/-1"><div class="spinner"></div><p>Loading\u2026</p></div>';
    return;
  }

  if (state.contacts.length === 0) {
    grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1">'
      + '<span class="material-icons-round">people_outline</span>'
      + '<p>No contacts yet. Tap the green + button to add your first contact.</p>'
      + '</div>';
    return;
  }

  grid.innerHTML = state.contacts.map(contact => {
    const color    = getAvatarColor(contact.name);
    const initials = getInitials(contact.name);
    const id       = contact.id;
    const hasPhone = !!contact.phone;

    // Action buttons: Call + WhatsApp Chat + SMS (all for contacts with phones)
    let btns = '';
    if (hasPhone) {
      btns += '<button class="card-btn card-btn-call" onclick="handleAction(\'call\',' + id + ')">'
        + '<span class="material-icons-round">call</span>Call</button>';
      btns += '<button class="card-btn card-btn-chat" onclick="handleAction(\'whatsapp\',' + id + ')">'
        + '<span class="material-icons-round">chat</span>Chat</button>';
      btns += '<button class="card-btn card-btn-sms" onclick="handleAction(\'sms\',' + id + ')">'
        + '<span class="material-icons-round">message</span>SMS</button>';
    }

    return '<div class="contact-card">'
      + '<div class="card-avatar" style="background:' + color + '">' + esc(initials) + '</div>'
      + '<div class="card-name">' + esc(contact.name) + '</div>'
      + '<div class="card-sub">' + esc(contact.phone || (contact.email || '')) + '</div>'
      + (btns ? '<div class="card-actions">' + btns + '</div>' : '')
      + '</div>';
  }).join('');

  // SOS card
  const sosNumber = localStorage.getItem('sos-number');
  const sosName   = localStorage.getItem('sos-name') || 'Emergency contact';
  const sosCard   = document.getElementById('sos-card');
  const sosSub    = document.getElementById('sos-subtitle');
  if (sosCard) {
    if (sosNumber) {
      sosCard.classList.remove('hidden');
      if (sosSub) sosSub.textContent = sosName + ' \u2014 ' + sosNumber;
    } else {
      sosCard.classList.add('hidden');
    }
  }
}

// ============================================================
// RENDER: MANAGE CONTACTS (list style, with delete)
// ============================================================
function renderManage() {
  const list = document.getElementById('manage-list');
  if (!list) return;

  const query    = state.manageQuery.toLowerCase();
  const filtered = state.contacts.filter(c =>
    c.name.toLowerCase().includes(query) ||
    (c.phone && c.phone.includes(query))
  );

  if (state.loading) {
    list.innerHTML = '<div class="loading-spinner"><div class="spinner"></div><p>Loading\u2026</p></div>';
    return;
  }

  if (filtered.length === 0) {
    const msg = state.contacts.length === 0
      ? 'No contacts yet. Tap the + button above to add your first contact.'
      : 'No contacts match your search.';
    list.innerHTML = '<div class="empty-state"><span class="material-icons-round">search_off</span><p>' + esc(msg) + '</p></div>';
    return;
  }

  list.innerHTML = filtered.map(contact => {
    const color    = getAvatarColor(contact.name);
    const initials = getInitials(contact.name);
    const id       = contact.id;
    return '<div class="manage-row">'
      + '<div class="manage-avatar" style="background:' + color + '">' + esc(initials) + '</div>'
      + '<div class="manage-info">'
      + '<div class="manage-name">' + esc(contact.name) + '</div>'
      + '<div class="manage-phone">' + esc(contact.phone || contact.email || '') + '</div>'
      + '</div>'
      + '<button class="manage-delete-btn" onclick="confirmDelete(' + id + ')" title="Delete">'
      + '<span class="material-icons-round">delete</span>'
      + '</button>'
      + '</div>';
  }).join('');
}

function filterManage(query) {
  state.manageQuery = query;
  renderManage();
}

function updateContactCount() {
  const el = document.getElementById('contact-count-label');
  if (el) {
    const n = state.contacts.length;
    el.textContent = n === 0 ? 'No contacts' : n + ' contact' + (n === 1 ? '' : 's');
  }
}

// ============================================================
// RENDER: KEYPAD
// ============================================================
let keypadDigits = '';

function renderKeypad() {
  const el = document.getElementById('keypad-digits');
  if (el) el.textContent = keypadDigits || '\u00a0'; // non-breaking space to keep height
}

function keypadPress(digit) {
  keypadDigits += digit;
  renderKeypad();
}

function keypadDelete() {
  keypadDigits = keypadDigits.slice(0, -1);
  renderKeypad();
}

function keypadClear() {
  keypadDigits = '';
  renderKeypad();
}

function keypadCall() {
  if (keypadDigits) {
    window.location.href = 'tel:' + keypadDigits;
  } else {
    showToast('Enter a number to call');
  }
}

function keypadSMS() {
  if (keypadDigits) {
    window.location.href = 'sms:' + keypadDigits;
  } else {
    showToast('Enter a number to message');
  }
}

function initKeypadLongPress() {
  const btn = document.getElementById('keypad-delete-btn');
  if (!btn) return;
  let timer = null;
  const startLong = function() { timer = setTimeout(function() { keypadClear(); }, 700); };
  const stopLong  = function() { clearTimeout(timer); };
  btn.addEventListener('mousedown',  startLong);
  btn.addEventListener('touchstart', startLong, { passive: true });
  btn.addEventListener('mouseup',    stopLong);
  btn.addEventListener('mouseleave', stopLong);
  btn.addEventListener('touchend',   stopLong);
}

// ============================================================
// RENDER: MESSAGES
// ============================================================
function renderMessages() {
  const list = document.getElementById('messages-list');
  if (!list) return;

  const withPhone = state.contacts.filter(c => c.phone);
  if (withPhone.length === 0) {
    list.innerHTML = '<div class="empty-state"><span class="material-icons-round">chat_bubble_outline</span><p>No contacts with phone numbers. Add contacts in Settings.</p></div>';
    return;
  }

  list.innerHTML = withPhone.map(contact => {
    const color    = getAvatarColor(contact.name);
    const initials = getInitials(contact.name);
    const id       = contact.id;
    return '<div class="message-row">'
      + '<div class="message-avatar" style="background:' + color + '">' + esc(initials) + '</div>'
      + '<div class="message-info">'
      + '<div class="message-name">' + esc(contact.name) + '</div>'
      + '<div class="message-phone">' + esc(contact.phone) + '</div>'
      + '</div>'
      + '<button class="message-sms-btn" onclick="handleAction(\'sms\',' + id + ')">'
      + '<span class="material-icons-round">message</span>SMS</button>'
      + '</div>';
  }).join('');
}

// ============================================================
// CONTACT ACTIONS (by ID — no string injection risk)
// ============================================================
function handleAction(action, contactId) {
  const contact = state.contacts.find(c => c.id === contactId);
  if (!contact) return;
  if (!contact.phone && (action === 'call' || action === 'sms' || action === 'whatsapp')) {
    showToast('No phone number for this contact');
    return;
  }

  switch (action) {
    case 'call':
      window.location.href = 'tel:' + contact.phone;
      break;
    case 'sms':
      window.location.href = 'sms:' + contact.phone;
      break;
    case 'whatsapp': {
      const digits = contact.phone.replace(/\D/g, '');
      window.open('https://wa.me/' + digits, '_blank');
      break;
    }
    case 'video':
      window.location.href = 'facetime:' + contact.phone;
      break;
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

function saveSosNumber(value) {
  const trimmed = value.trim();
  if (trimmed) {
    localStorage.setItem('sos-number', trimmed);
  } else {
    localStorage.removeItem('sos-number');
  }
  renderContacts();
}

function saveSosName(value) {
  const trimmed = value.trim();
  if (trimmed) {
    localStorage.setItem('sos-name', trimmed);
  } else {
    localStorage.removeItem('sos-name');
  }
  renderContacts();
}

function loadSosInputs() {
  const numInput  = document.getElementById('sos-number-input');
  const nameInput = document.getElementById('sos-name-input');
  if (numInput)  numInput.value  = localStorage.getItem('sos-number') || '';
  if (nameInput) nameInput.value = localStorage.getItem('sos-name') || '';
}

// ============================================================
// ADD CONTACT MODAL
// ============================================================
function showAddContactModal() {
  document.getElementById('add-contact-modal').classList.remove('hidden');
  setTimeout(function() {
    document.getElementById('contact-name-input').focus();
  }, 150);
}

function hideAddContactModal() {
  document.getElementById('add-contact-modal').classList.add('hidden');
  document.getElementById('add-contact-form').reset();
}

async function submitAddContact(e) {
  e.preventDefault();
  const name  = document.getElementById('contact-name-input').value.trim();
  const phone = document.getElementById('contact-phone-input').value.trim();
  const email = document.getElementById('contact-email-input').value.trim();
  if (!name) return;

  const submitBtn = document.getElementById('add-contact-submit');
  submitBtn.disabled = true;
  submitBtn.textContent = 'Adding\u2026';

  try {
    await createContact({ name, phone: phone || null, email: email || null });
    hideAddContactModal();
    renderContacts();
    renderManage();
    updateContactCount();
    showToast('Contact added');
  } catch (err) {
    showToast('Failed to add contact: ' + err.message);
  } finally {
    submitBtn.disabled = false;
    submitBtn.textContent = 'Add Contact';
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
      renderManage();
      renderContacts();
      updateContactCount();
      showToast('Contact deleted');
    })
    .catch(function() { showToast('Failed to delete contact'); });
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
    showToast('Open in browser menu \u2192 Add to Home Screen');
  }
}

// ============================================================
// TOAST
// ============================================================
let toastTimer = null;

function showToast(message, duration) {
  if (duration === undefined) duration = 2500;
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.classList.remove('hidden');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(function() { toast.classList.add('hidden'); }, duration);
}

// ============================================================
// INIT
// ============================================================
async function init() {
  // Clock
  updateClock();
  setInterval(updateClock, 30000);

  // Service Worker
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').catch(function(err) {
      console.warn('SW registration failed:', err);
    });
  }

  // PWA install prompt
  window.addEventListener('beforeinstallprompt', function(e) {
    e.preventDefault();
    state.installPrompt = e;
    const dismissed = localStorage.getItem('install-dismissed');
    if (!dismissed) {
      document.getElementById('install-banner').classList.remove('hidden');
    }
  });

  document.getElementById('install-btn').addEventListener('click', showInstallPrompt);

  document.getElementById('dismiss-install').addEventListener('click', function() {
    document.getElementById('install-banner').classList.add('hidden');
    localStorage.setItem('install-dismissed', '1');
  });

  // Close modal on overlay tap
  document.getElementById('add-contact-modal').addEventListener('click', function(e) {
    if (e.target === this) hideAddContactModal();
  });

  initKeypadLongPress();

  // Load data
  await loadContacts();
}

document.addEventListener('DOMContentLoaded', init);
