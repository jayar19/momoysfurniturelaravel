// API Configuration
// Laravel serves the frontend and backend from the same app, so keep API calls relative.
const API_BASE_URL = '/api';

console.log('🔗 API Base URL:', API_BASE_URL);
console.log('📍 Current URL:', window.location.href);

window.API_BASE_URL = API_BASE_URL;


// Test backend connection on load
fetch(`${API_BASE_URL}/health`)
  .then(res => res.json())
  .then(data => {
    console.log('✅ Backend Status:', data.status);
    console.log('📡 Session ID:', data.sessionId);
    console.log('🕐 Timestamp:', data.timestamp);
  })
  .catch(err => {
    console.error('❌ Backend Connection Failed:', err.message);
    console.error('💡 Trying to connect to:', API_BASE_URL);
    console.error('💡 Make sure backend routes are configured correctly');
  });
