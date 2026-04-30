// PostgreSQL/Node auth client. This keeps the old Firebase-shaped frontend calls working
// while the backend now owns users, passwords, roles, and signed auth tokens.
const AUTH_STORAGE_KEY = 'momoys_auth_session';

function resolveApiUrl(url) {
  if (url.startsWith('http')) return url;
  if (url.startsWith('/api/')) return url;
  const configuredBase = window.API_BASE_URL || (typeof API_BASE_URL !== 'undefined' ? API_BASE_URL : '/api');
  const base = configuredBase.replace(/\/$/, '');
  return `${base}/${url.replace(/^\//, '')}`;
}

function readSession() {
  try {
    return JSON.parse(localStorage.getItem(AUTH_STORAGE_KEY) || 'null');
  } catch (error) {
    return null;
  }
}

function writeSession(session) {
  if (!session || !session.token || !session.user) {
    localStorage.removeItem(AUTH_STORAGE_KEY);
    return;
  }
  localStorage.setItem(AUTH_STORAGE_KEY, JSON.stringify(session));
}

function makeAuthError(payload, fallbackCode) {
  const error = new Error(payload?.error || 'Authentication failed');
  error.code = payload?.code || fallbackCode || 'auth/invalid-credential';
  return error;
}

function makeUser(user, token) {
  return {
    ...user,
    uid: user.uid || user.id,
    displayName: user.fullName || user.name || '',
    getIdToken: async () => token
  };
}

const authListeners = [];
let currentSession = readSession();

const auth = {
  currentUser: currentSession ? makeUser(currentSession.user, currentSession.token) : null,

  onAuthStateChanged(callback) {
    authListeners.push(callback);
    setTimeout(() => callback(auth.currentUser), 0);
    return () => {
      const index = authListeners.indexOf(callback);
      if (index >= 0) authListeners.splice(index, 1);
    };
  },

  async signInWithEmailAndPassword(email, password) {
    const response = await fetch(resolveApiUrl('/auth/login'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password })
    });
    const payload = await response.json();
    if (!response.ok) throw makeAuthError(payload, 'auth/invalid-credential');

    setCurrentSession(payload);
    return { user: auth.currentUser };
  },

  async createUserWithEmailAndPassword(email, password) {
    const fullName =
      document.getElementById('reg-fullName')?.value?.trim() ||
      document.getElementById('fullName')?.value?.trim() ||
      '';

    const response = await fetch(resolveApiUrl('/auth/register'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password, fullName })
    });
    const payload = await response.json();
    if (!response.ok) throw makeAuthError(payload, payload.code || 'auth/registration-failed');

    setCurrentSession(payload);
    return { user: auth.currentUser };
  },

  async signOut() {
    const token = await getAuthToken();
    if (token) {
      fetch(resolveApiUrl('/auth/logout'), {
        method: 'POST',
        headers: { Authorization: `Bearer ${token}` }
      }).catch(() => {});
    }
    setCurrentSession(null);
  }
};

function setCurrentSession(session) {
  currentSession = session;
  writeSession(session);
  auth.currentUser = session ? makeUser(session.user, session.token) : null;
  authListeners.forEach((callback) => callback(auth.currentUser));
  updateUIForAuthState(auth.currentUser);
}

async function getAuthToken() {
  if (currentSession?.token) return currentSession.token;
  const stored = readSession();
  return stored?.token || null;
}

async function authenticatedFetch(url, options = {}) {
  const token = await getAuthToken();
  const headers = {
    'Content-Type': 'application/json',
    ...(options.headers || {})
  };

  if (token) {
    headers.Authorization = `Bearer ${token}`;
  }

  return fetch(resolveApiUrl(url), {
    ...options,
    headers
  });
}

const db = {
  collection(name) {
    return {
      doc(id) {
        return {
          async get() {
            const token = await getAuthToken();
            const currentUid = auth.currentUser?.uid;
            const path = name === 'users' && id === currentUid ? '/auth/me' : `/${name}/${encodeURIComponent(id)}`;
            const response = await authenticatedFetch(path, {
              headers: token ? { Authorization: `Bearer ${token}` } : {}
            });
            if (response.status === 404) {
              return { exists: false, data: () => undefined };
            }
            if (!response.ok) throw new Error('Unable to load document');
            const payload = await response.json();
            return {
              id: payload.id || id,
              exists: true,
              data: () => payload
            };
          },

          async set(data) {
            if (name !== 'users' || id !== auth.currentUser?.uid) {
              throw new Error('Client document writes are only supported for the current user profile');
            }
            const response = await authenticatedFetch('/auth/profile', {
              method: 'PUT',
              body: JSON.stringify(data)
            });
            if (!response.ok) throw new Error('Unable to save profile');
            const user = await response.json();
            if (currentSession) {
              setCurrentSession({ token: currentSession.token, user });
            }
          }
        };
      }
    };
  }
};

function updateUIForAuthState(user) {
  const authLinks = document.querySelectorAll('.auth-required');
  const guestLinks = document.querySelectorAll('.guest-only');
  const userEmail = document.getElementById('user-email');

  if (user) {
    authLinks.forEach((link) => {
      link.style.display = 'block';
      link.classList.add('visible');
      link.classList.remove('hidden');
    });
    guestLinks.forEach((link) => {
      link.style.display = 'none';
      link.classList.add('hidden');
      link.classList.remove('visible');
    });
    if (userEmail) userEmail.textContent = user.email;
  } else {
    authLinks.forEach((link) => {
      link.style.display = 'none';
      link.classList.remove('visible');
      link.classList.add('hidden');
    });
    guestLinks.forEach((link) => {
      link.style.display = 'block';
      link.classList.remove('hidden');
      link.classList.add('visible');
    });
  }
}

console.log('PostgreSQL auth client initialized');
