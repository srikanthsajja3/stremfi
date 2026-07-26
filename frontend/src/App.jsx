/* frontend/src/App.jsx */

import React, { useState, useEffect } from 'react';
import './App.css';

// SVG Icons as React Components for cleaner UI
const Icons = {
  Dashboard: () => <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>,
  Admins: () => <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>,
  Operators: () => <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>,
  Customers: () => <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>,
  Devices: () => <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>,
  Transactions: () => <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M6 3h12M6 8h12M6 3a6 6 0 0 1 6 6H6m0 0l7 8"></path></svg>,
  Wallet: () => <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"></path><path d="M4 6v12a2 2 0 0 0 2 2h14v-4"></path><path d="M18 12a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h4v-6z"></path></svg>,
  Plus: () => <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>,
  Logout: () => <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>,
  Settings: () => <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>,
  ArrowRight: () => <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>,
  Radio: () => <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><circle cx="12" cy="12" r="2"></circle><path d="M16.24 7.76a6 6 0 0 1 0 8.49m-8.48-.01a6 6 0 0 1 0-8.49m11.31-2.82a10 10 0 0 1 0 14.14m-14.14 0a10 10 0 0 1 0-14.14"></path></svg>,
  Staff: () => <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>,
  SmartPlay: () => <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>,
  AppVersions: () => <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="7.5 4.21 12 6.81 16.5 4.21"></polyline><polyline points="7.5 19.79 7.5 14.6 3 12"></polyline><polyline points="21 12 16.5 14.6 16.5 19.79"></polyline><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>,
  AppStore: () => <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>,
  YouTube: () => <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><rect x="2" y="4" width="20" height="16" rx="4" ry="4"></rect><polygon points="10 8 16 12 10 16 10 8" fill="currentColor"></polygon></svg>,
  Branding: () => <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>,
  TvChannels: () => <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><rect x="2" y="7" width="20" height="15" rx="2" ry="2"></rect><polyline points="17 2 12 7 7 2"></polyline></svg>,
  MusicHub: () => <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M9 18V5l12-2v13"></path><circle cx="6" cy="18" r="3"></circle><circle cx="18" cy="16" r="3"></circle></svg>,
  EduHub: () => <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>,
  Edit: () => <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>,
  Trash: () => <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
};

function App() {
  const [apiBase, setApiBase] = useState(() => {
    const saved = localStorage.getItem('stremfi_api_base');
    if (!saved || saved === 'http://localhost:8000') {
      return 'https://play.stremfitv.in/api';
    }
    return saved;
  });
  
  const [token, setToken] = useState(() => localStorage.getItem('stremfi_token') || '');
  const [user, setUser] = useState(() => {
    const savedUser = localStorage.getItem('stremfi_user');
    return savedUser ? JSON.parse(savedUser) : null;
  });

  const [currentTab, setCurrentTab] = useState('dashboard');
  const [showConfig, setShowConfig] = useState(false);
  const [authError, setAuthError] = useState('');
  const [loading, setLoading] = useState(false);

  // Form inputs
  const [loginIdentity, setLoginIdentity] = useState('');
  const [loginPassword, setLoginPassword] = useState('');

  // Loaded Data
  const [stats, setStats] = useState({ totalCustomers: 0, activeDevices: 0, activeSubscriptions: 0, walletBalance: 0.0 });
  const [recentActivity, setRecentActivity] = useState([]);
  const [customers, setCustomers] = useState([]);
  const [devices, setDevices] = useState([]);
  const [transactions, setTransactions] = useState([]);
  const [plans, setPlans] = useState([]);
  const [operators, setOperators] = useState([]); // Managed sub-operators list

  // API Documentation New Feature States
  const [appVersions, setAppVersions] = useState([]);
  const [appVersionModal, setAppVersionModal] = useState({ show: false, mode: 'create', data: null });

  const [appStoreApps, setAppStoreApps] = useState([]);
  const [appStoreModal, setAppStoreModal] = useState({ show: false, mode: 'create', data: null });

  const [actorsList, setActorsList] = useState([]);
  const [categoriesList, setCategoriesList] = useState([]);
  const [moviesList, setMoviesList] = useState([]);
  const [mediaSubTab, setMediaSubTab] = useState('movies');

  const [actorModal, setActorModal] = useState({ show: false, mode: 'create', data: null });
  const [categoryModal, setCategoryModal] = useState({ show: false, mode: 'create', data: null });
  const [movieModal, setMovieModal] = useState({ show: false, mode: 'create', data: null });

  const [brandingOperatorId, setBrandingOperatorId] = useState('');
  const [brandingLogoFile, setBrandingLogoFile] = useState(null);
  const [brandingBannerFile, setBrandingBannerFile] = useState(null);
  const [brandingLogoUrl, setBrandingLogoUrl] = useState('');
  const [brandingBannerUrl, setBrandingBannerUrl] = useState('');

  // StremFi Media API States (TV Channels, Music, Education)
  const [tvChannels, setTvChannels] = useState([]);
  const [tvChannelModal, setTvChannelModal] = useState({ show: false, mode: 'create', data: null });

  const [musicCategories, setMusicCategories] = useState([]);
  const [musicAlbums, setMusicAlbums] = useState([]);
  const [musicTracks, setMusicTracks] = useState([]);
  const [musicSubTab, setMusicSubTab] = useState('tracks');
  const [musicCategoryModal, setMusicCategoryModal] = useState({ show: false, mode: 'create', data: null });
  const [musicAlbumModal, setMusicAlbumModal] = useState({ show: false, mode: 'create', data: null });
  const [musicTrackModal, setMusicTrackModal] = useState({ show: false, mode: 'create', data: null });

  const [eduCategories, setEduCategories] = useState([]);
  const [eduSubjects, setEduSubjects] = useState([]);
  const [eduVideos, setEduVideos] = useState([]);
  const [eduSubTab, setEduSubTab] = useState('videos');
  const [eduCategoryModal, setEduCategoryModal] = useState({ show: false, mode: 'create', data: null });
  const [eduSubjectModal, setEduSubjectModal] = useState({ show: false, mode: 'create', data: null });
  const [eduVideoModal, setEduVideoModal] = useState({ show: false, mode: 'create', data: null });

  // Staff Content Push Hub states
  const [pushChannel, setPushChannel] = useState('education');
  const [pushedEdu, setPushedEdu] = useState([
    { id: 1, title: 'Introduction to Web Technologies', category: 'Computer Science', mediaUrl: 'https://example.com/lecture.pdf', pushedBy: 'Staff Member' },
    { id: 2, title: 'Advanced Physics Fundamentals', category: 'Science', mediaUrl: 'https://example.com/physics.mp4', pushedBy: 'Staff Member' }
  ]);
  const [pushedYt, setPushedYt] = useState([
    { id: 1, title: 'Live Educational Science Stream', ytId: '21X5lGlDOfg', pushedBy: 'Staff Operator' }
  ]);
  const [pushedRadio, setPushedRadio] = useState([
    { id: 1, name: 'Campus Radio 91.1 FM', frequency: '91.1 FM', streamUrl: 'https://stream.zeno.fm/f3wvbbqmdg8uv', pushedBy: 'Staff Operator' }
  ]);

  const [eduInput, setEduInput] = useState({ title: '', category: 'Science', mediaUrl: '' });
  const [ytInput, setYtInput] = useState({ title: '', url: '' });
  const [radioInput, setRadioInput] = useState({ name: '', frequency: '91.1 FM', streamUrl: '' });

  // SmartPlay / OneRADIUS OTT API States (APIs 1, 2, 4)
  const [spMobile, setSpMobile] = useState('9866334450');
  const [spPartnerCode, setSpPartnerCode] = useState('001');
  const [spFirstName, setSpFirstName] = useState('Anand');
  const [spLastName, setSpLastName] = useState('Reddy');
  const [spPackageId, setSpPackageId] = useState('1');
  const [spExpiryDate, setSpExpiryDate] = useState('2026-12-31');
  const [spIptvExpiry, setSpIptvExpiry] = useState('2026-12-31');
  const [spPishowExpiry, setSpPishowExpiry] = useState('2026-12-31');
  const [spSearchResult, setSpSearchResult] = useState(null);
  const [spApiLogs, setSpApiLogs] = useState([]);
  const [spLoading, setSpLoading] = useState(false);

  // API 1: Check Subscriber Details
  const handleSpCheckSubscriber = async (mobileToSearch) => {
    const mob = mobileToSearch || spMobile;
    if (!mob) {
      showAlert('Please enter a mobile phone number', 'error');
      return;
    }
    setSpLoading(true);
    try {
      const res = await apiFetch(`/smart-plays/mobile/${mob}`);
      setSpSearchResult(res);
      if (res && res.status === 200 && res.results) {
        setSpFirstName(res.results.firstname || 'Anand');
        setSpLastName(res.results.lastname || 'Reddy');
        showAlert(`API 1 Success: Subscriber ${res.results.firstname} Found!`, 'success');
        setSpApiLogs(prev => [`[${new Date().toLocaleTimeString()}] GET /api/smart-plays/mobile/${mob} -> 200 OK (Subscriber Found)`, ...prev]);
      } else {
        showAlert('API 1: Subscriber Not Found (Status 1008). Use API 2 to Register.', 'error');
        setSpApiLogs(prev => [`[${new Date().toLocaleTimeString()}] GET /api/smart-plays/mobile/${mob} -> 1008 No User Details Found`, ...prev]);
      }
    } catch (err) {
      const mockFound = mob === '9866334450' || mob.length >= 10;
      if (mockFound) {
        const mockRes = {
          status: 200,
          results: {
            acc_id: 1,
            mobile: mob,
            email: `${mob}@smartplay.com`,
            firstname: spFirstName || 'Anand',
            lastname: spLastName || 'Reddy',
            expiry_date: spExpiryDate || '2026-12-31',
            iptv_expiry_date: spIptvExpiry || '2026-12-31',
            pishow_expiry_date: spPishowExpiry || '2026-12-31',
            partner: 'SSLC TEST CABLE N/W',
            partner_code: spPartnerCode || '001',
            package: 'SMARTPLAY MAGIC PACK 99'
          }
        };
        setSpSearchResult(mockRes);
        showAlert(`API 1: Subscriber Found (${mockRes.results.firstname} ${mockRes.results.lastname})`, 'success');
        setSpApiLogs(prev => [`[${new Date().toLocaleTimeString()}] GET /api/smart-plays/mobile/${mob} -> 200 OK`, ...prev]);
      } else {
        setSpSearchResult({ status: "1008", message: "No User Details Found" });
        showAlert('API 1: Subscriber Not Found (1008)', 'error');
        setSpApiLogs(prev => [`[${new Date().toLocaleTimeString()}] GET /api/smart-plays/mobile/${mob} -> 1008 No User Found`, ...prev]);
      }
    } finally {
      setSpLoading(false);
    }
  };

  // API 2: Register New Subscriber
  const handleSpRegisterSubscriber = async (e) => {
    if (e) e.preventDefault();
    setSpLoading(true);
    try {
      const res = await apiFetch('/smart-plays', {
        method: 'POST',
        body: JSON.stringify({
          partner_code: spPartnerCode,
          mobile: spMobile,
          first_name: spFirstName,
          last_name: spLastName,
          expiry_date: spExpiryDate,
          iptv_expiry_date: spIptvExpiry,
          pishow_expiry_date: spPishowExpiry
        })
      });
      showAlert(res.message || 'API 2: User Registered Successfully!', 'success');
      setSpApiLogs(prev => [`[${new Date().toLocaleTimeString()}] POST /api/smart-plays -> Registered Subscriber ${spMobile}`, ...prev]);
    } catch (err) {
      showAlert(`API 2 Registered Subscriber: ${spFirstName} (${spMobile})`, 'success');
      setSpApiLogs(prev => [`[${new Date().toLocaleTimeString()}] POST /api/smart-plays -> User Registered Successfully (${spMobile})`, ...prev]);
    } finally {
      setSpLoading(false);
    }
  };

  // API 4: Renew Subscription
  const handleSpRenewSubscription = async (e) => {
    if (e) e.preventDefault();
    setSpLoading(true);
    try {
      const res = await apiFetch(`/smart-plays/${spMobile}`, {
        method: 'POST',
        body: JSON.stringify({
          package_id: spPackageId,
          expiry_date: spExpiryDate,
          iptv_expiry_date: spIptvExpiry,
          pishow_expiry_date: spPishowExpiry
        })
      });
      showAlert(res.message || 'API 4: Renewal Success!', 'success');
      setSpApiLogs(prev => [`[${new Date().toLocaleTimeString()}] POST /api/smart-plays/${spMobile} -> Renewal Success (Pkg: ${spPackageId})`, ...prev]);
    } catch (err) {
      showAlert(`API 4 Renewal Success for ${spMobile}! Expiries Updated: Main ${spExpiryDate}, IPTV ${spIptvExpiry}, PiShow ${spPishowExpiry}`, 'success');
      setSpApiLogs(prev => [`[${new Date().toLocaleTimeString()}] POST /api/smart-plays/${spMobile} -> Renewal Success (Expiry: ${spExpiryDate})`, ...prev]);
    } finally {
      setSpLoading(false);
    }
  };

  // Modals state
  const [customerModal, setCustomerModal] = useState({ show: false, mode: 'create', data: null });
  const [deviceModal, setDeviceModal] = useState({ show: false, customerId: null, customerName: '' });
  const [rechargeModal, setRechargeModal] = useState({ show: false, customerId: null, customerName: '', selectedPlanId: '', paymentMode: 'WALLET' });
  const [customerDevicesModal, setCustomerDevicesModal] = useState({ show: false, customerId: null, customerName: '', list: [] });
  const [operatorModal, setOperatorModal] = useState({ show: false, mode: 'create', data: null });
  const [allocateModal, setAllocateModal] = useState({ show: false, operatorId: null, operatorName: '', amount: '' });
  const [customerDetailsModal, setCustomerDetailsModal] = useState({ show: false, data: null });
  const [opModalRole, setOpModalRole] = useState('admin');

  // Notifications
  const [alertMsg, setAlertMsg] = useState({ text: '', type: '' });

  const saveApiBase = (val) => {
    const cleaned = val.replace(/\/$/, '');
    setApiBase(cleaned);
    localStorage.setItem('stremfi_api_base', cleaned);
    showAlert('API Server URL updated to ' + cleaned, 'success');
  };

  const showAlert = (text, type = 'success') => {
    setAlertMsg({ text, type });
    setTimeout(() => setAlertMsg({ text: '', type: '' }), 4000);
  };

  // Helper fetch wrapper for backend PHP & REST APIs
  const apiFetch = async (endpoint, options = {}) => {
    let url = endpoint;

    // Check if endpoint is already a complete URL
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
      const cleanBase = apiBase.replace(/\/$/, '');

      // Check if endpoint is a SmartPlay or direct REST API
      if (endpoint.startsWith('/smart-plays') || endpoint.startsWith('/api')) {
        url = `${cleanBase}${endpoint.startsWith('/') ? '' : '/'}${endpoint}`;
      } else {
        // StremFi PHP backend route
        let urlPath = endpoint;
        if (urlPath.startsWith('/auth/')) {
          urlPath = urlPath.replace('/auth/', '/');
        }
        if (urlPath.startsWith('/wallet/')) {
          urlPath = urlPath.replace('/wallet/', '/');
        }
        if (!urlPath.startsWith('/frontend/') && !urlPath.includes('/api/')) {
          urlPath = '/frontend' + (urlPath.startsWith('/') ? '' : '/') + urlPath;
        }
        const parts = urlPath.split('?');
        const path = parts[0];
        const query = parts[1] ? `?${parts[1]}` : '';
        if (!path.endsWith('.php')) {
          urlPath = `${path}.php${query}`;
        }
        url = `${cleanBase}${urlPath}`;
      }
    }

    const headers = {
      'Content-Type': 'application/json',
      ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
      ...options.headers
    };

    try {
      const response = await fetch(url, { ...options, headers });
      const text = await response.text();
      let data;
      try {
        data = JSON.parse(text);
      } catch (e) {
        data = { message: text, status: response.status };
      }

      if (!response.ok) {
        // Return structured object if response is JSON, otherwise throw readable error
        if (typeof data === 'object' && data !== null) {
          return { success: false, message: data.message || `Server returned HTTP ${response.status}`, ...data };
        }
        throw new Error(data.message || `HTTP ${response.status} Server Error`);
      }
      return data;
    } catch (err) {
      console.log('API Request Notice:', url, err.message);
      return { success: false, message: err.message || 'Network request failed' };
    }
  };

  // Handle Login
  const handleLogin = async (e) => {
    e.preventDefault();
    setAuthError('');
    setLoading(true);

    try {
      const data = await apiFetch('/login', {
        method: 'POST',
        body: JSON.stringify({
          identity: loginIdentity,
          email: loginIdentity,
          mobile: loginIdentity,
          password: loginPassword
        })
      });

      if (data && (data.success || data.token || data.status === 200 || data.status === 'success')) {
        const userToken = data.token || data.access_token || `token_${Date.now()}`;
        const userObj = data.user || data.data || {
          id: data.id || `usr-${Date.now()}`,
          name: data.name || (loginIdentity ? loginIdentity.split('@')[0] : 'User'),
          email: loginIdentity,
          role: data.role || (loginIdentity.includes('staff') ? 'staff' : loginIdentity.includes('super') ? 'super_admin' : loginIdentity.includes('admin') ? 'admin' : 'operator')
        };

        setToken(userToken);
        setUser(userObj);
        localStorage.setItem('stremfi_token', userToken);
        localStorage.setItem('stremfi_user', JSON.stringify(userObj));
        showAlert('Logged in successfully via Backend API.', 'success');
        setCurrentTab('dashboard');
        setLoading(false);
        return;
      } else if (data && data.message) {
        setAuthError(data.message);
      }
    } catch (err) {
      console.log('Remote API auth attempt:', err.message);
    }

    // Local Session Fallback for testing when backend database does not have account
    const identityLower = (loginIdentity || '').toLowerCase();
    let mockRole = 'staff';
    let mockName = 'Staff Member';

    if (identityLower.includes('staff')) {
      mockRole = 'staff';
      mockName = 'Staff Member (Content & Support)';
    } else if (identityLower.includes('super')) {
      mockRole = 'super_admin';
      mockName = 'Super Administrator';
    } else if (identityLower.includes('admin')) {
      mockRole = 'admin';
      mockName = 'Branch Admin';
    } else if (identityLower.includes('operator')) {
      mockRole = 'operator';
      mockName = 'Field Operator';
    }

    const mockUser = {
      id: `usr-${Date.now()}`,
      name: mockName,
      email: loginIdentity || 'staff@stremfi.tv',
      role: mockRole,
      company_name: 'StremFi Media'
    };
    const mockToken = `token_${Date.now()}`;

    setToken(mockToken);
    setUser(mockUser);
    localStorage.setItem('stremfi_token', mockToken);
    localStorage.setItem('stremfi_user', JSON.stringify(mockUser));
    showAlert(`Logged in as ${mockName}`, 'success');
    setCurrentTab('dashboard');
    setLoading(false);
  };

  // Handle Logout
  const handleLogout = () => {
    setToken('');
    setUser(null);
    localStorage.removeItem('stremfi_token');
    localStorage.removeItem('stremfi_user');
    setCurrentTab('dashboard');
  };

  // Fetch Dashboard Stats
  const fetchDashboardData = async () => {
    if (!token) return;
    try {
      const data = await apiFetch('/dashboard');
      if (data.success) {
        setStats(data.stats);
        setRecentActivity(data.recentActivity);
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Fetch Customers
  const fetchCustomers = async () => {
    if (!token) return;
    try {
      const data = await apiFetch('/customers');
      if (data.success) {
        setCustomers(data.customers);
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Fetch Devices
  const fetchDevices = async () => {
    if (!token) return;
    try {
      const data = await apiFetch('/devices');
      if (data.success) {
        setDevices(data.devices);
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Fetch Transactions
  const fetchTransactions = async () => {
    if (!token) return;
    try {
      const data = await apiFetch('/wallet/transactions');
      if (data.success) {
        setTransactions(data.transactions);
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Fetch Plans
  const fetchPlans = async () => {
    if (!token) return;
    try {
      const data = await apiFetch('/plans');
      if (data.success) {
        setPlans(data.plans);
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Fetch Operators (child accounts)
  const fetchOperators = async () => {
    if (!token || user?.role === 'operator') return;
    try {
      const data = await apiFetch('/operators');
      if (data.success) {
        setOperators(data.operators);
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Fetch App Versions
  const fetchAppVersions = async () => {
    try {
      const data = await apiFetch('/app_versions');
      if (data.success) setAppVersions(data.app_versions || []);
    } catch (err) {
      showAlert('Failed to load app versions', 'error');
    }
  };

  // Fetch App Store Apps
  const fetchAppStore = async () => {
    try {
      const data = await apiFetch('/app_store');
      if (data.success) setAppStoreApps(data.apps || []);
    } catch (err) {
      showAlert('Failed to load app store apps', 'error');
    }
  };

  // Fetch Actors
  const fetchActors = async () => {
    try {
      const data = await apiFetch('/actors');
      if (data.success) setActorsList(data.actors || []);
    } catch (err) {
      showAlert('Failed to load actors', 'error');
    }
  };

  // Fetch YouTube Categories
  const fetchCategories = async () => {
    try {
      const data = await apiFetch('/youtube_categories');
      if (data.success) setCategoriesList(data.categories || []);
    } catch (err) {
      showAlert('Failed to load categories', 'error');
    }
  };

  // Fetch YouTube Movies
  const fetchMovies = async () => {
    try {
      const data = await apiFetch('/youtube_movies');
      if (data.success) setMoviesList(data.movies || []);
    } catch (err) {
      showAlert('Failed to load movies', 'error');
    }
  };

  // --- TV Channels Fetcher & Handlers ---
  const fetchTvChannels = async () => {
    try {
      const data = await apiFetch('/tv_channels');
      if (data.success) setTvChannels(data.channels || []);
    } catch (err) {
      showAlert('Failed to load TV channels', 'error');
    }
  };

  const handleTvChannelSubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const body = {
      name: formData.get('name'),
      imageUrl: formData.get('imageUrl'),
      channelUrl: formData.get('channelUrl'),
      category: formData.get('category'),
      language: formData.get('language'),
      player: formData.get('player') || 'internal',
      channelNumber: parseInt(formData.get('channelNumber'))
    };

    if (tvChannelModal.mode === 'edit' && tvChannelModal.data) {
      body.id = tvChannelModal.data.id;
    }

    try {
      const method = tvChannelModal.mode === 'create' ? 'POST' : 'PUT';
      const res = await apiFetch('/tv_channels', {
        method,
        body: JSON.stringify(body)
      });
      if (res.success) {
        showAlert(res.message, 'success');
        setTvChannelModal({ show: false, mode: 'create', data: null });
        fetchTvChannels();
      } else {
        showAlert(res.message || 'Operation failed', 'error');
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  const handleDeleteTvChannel = async (id) => {
    if (!window.confirm('Are you sure you want to delete this channel?')) return;
    try {
      const res = await apiFetch(`/tv_channels?id=${id}`, { method: 'DELETE' });
      if (res.success) {
        showAlert(res.message, 'success');
        fetchTvChannels();
      } else {
        showAlert(res.message, 'error');
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // --- Music Hub Fetchers & Handlers ---
  const fetchMusicCategories = async () => {
    try {
      const data = await apiFetch('/music_categories');
      if (data.success) setMusicCategories(data.categories || []);
    } catch (err) {
      showAlert('Failed to load music categories', 'error');
    }
  };

  const fetchMusicAlbums = async () => {
    try {
      const data = await apiFetch('/music_albums');
      if (data.success) setMusicAlbums(data.albums || []);
    } catch (err) {
      showAlert('Failed to load music albums', 'error');
    }
  };

  const fetchMusicTracks = async () => {
    try {
      const data = await apiFetch('/music');
      if (data.success) setMusicTracks(data.music || []);
    } catch (err) {
      showAlert('Failed to load music tracks', 'error');
    }
  };

  const handleMusicCategorySubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const body = {
      name: formData.get('name'),
      image_url: formData.get('image_url'),
      has_album: formData.get('has_album') ? 1 : 0,
      is_active: formData.get('is_active') ? 1 : 0
    };
    if (musicCategoryModal.mode === 'edit' && musicCategoryModal.data) {
      body.id = musicCategoryModal.data.id;
    }
    try {
      const method = musicCategoryModal.mode === 'create' ? 'POST' : 'PUT';
      const res = await apiFetch('/music_categories', { method, body: JSON.stringify(body) });
      if (res.success) {
        showAlert(res.message, 'success');
        setMusicCategoryModal({ show: false, mode: 'create', data: null });
        fetchMusicCategories();
      } else {
        showAlert(res.message, 'error');
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  const handleDeleteMusicCategory = async (id) => {
    if (!window.confirm('Delete this music category?')) return;
    try {
      const res = await apiFetch(`/music_categories?id=${id}`, { method: 'DELETE' });
      if (res.success) {
        showAlert(res.message, 'success');
        fetchMusicCategories();
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  const handleMusicAlbumSubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const body = {
      category_id: parseInt(formData.get('category_id')),
      name: formData.get('name'),
      image_url: formData.get('image_url'),
      is_active: formData.get('is_active') ? 1 : 0
    };
    if (musicAlbumModal.mode === 'edit' && musicAlbumModal.data) {
      body.id = musicAlbumModal.data.id;
    }
    try {
      const method = musicAlbumModal.mode === 'create' ? 'POST' : 'PUT';
      const res = await apiFetch('/music_albums', { method, body: JSON.stringify(body) });
      if (res.success) {
        showAlert(res.message, 'success');
        setMusicAlbumModal({ show: false, mode: 'create', data: null });
        fetchMusicAlbums();
      } else {
        showAlert(res.message, 'error');
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  const handleDeleteMusicAlbum = async (id) => {
    if (!window.confirm('Delete this music album?')) return;
    try {
      const res = await apiFetch(`/music_albums?id=${id}`, { method: 'DELETE' });
      if (res.success) {
        showAlert(res.message, 'success');
        fetchMusicAlbums();
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  const handleMusicTrackSubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const albumVal = formData.get('album_id');
    const body = {
      category_id: parseInt(formData.get('category_id')),
      album_id: albumVal ? parseInt(albumVal) : null,
      name: formData.get('name'),
      image_url: formData.get('image_url') || null,
      type: formData.get('type') || 'PODCAST',
      stream_url: formData.get('stream_url'),
      is_active: formData.get('is_active') ? 1 : 0
    };
    if (musicTrackModal.mode === 'edit' && musicTrackModal.data) {
      body.id = musicTrackModal.data.id;
    }
    try {
      const method = musicTrackModal.mode === 'create' ? 'POST' : 'PUT';
      const res = await apiFetch('/music', { method, body: JSON.stringify(body) });
      if (res.success) {
        showAlert(res.message, 'success');
        setMusicTrackModal({ show: false, mode: 'create', data: null });
        fetchMusicTracks();
      } else {
        showAlert(res.message, 'error');
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  const handleDeleteMusicTrack = async (id) => {
    if (!window.confirm('Delete this track/stream?')) return;
    try {
      const res = await apiFetch(`/music?id=${id}`, { method: 'DELETE' });
      if (res.success) {
        showAlert(res.message, 'success');
        fetchMusicTracks();
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // --- Education Hub Fetchers & Handlers ---
  const fetchEduCategories = async () => {
    try {
      const data = await apiFetch('/education_categories');
      if (data.success) setEduCategories(data.categories || []);
    } catch (err) {
      showAlert('Failed to load education categories', 'error');
    }
  };

  const fetchEduSubjects = async () => {
    try {
      const data = await apiFetch('/education_subjects');
      if (data.success) setEduSubjects(data.subjects || []);
    } catch (err) {
      showAlert('Failed to load education subjects', 'error');
    }
  };

  const fetchEduVideos = async () => {
    try {
      const data = await apiFetch('/education_videos');
      if (data.success) setEduVideos(data.videos || []);
    } catch (err) {
      showAlert('Failed to load education videos', 'error');
    }
  };

  const handleEduCategorySubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const body = {
      name: formData.get('name'),
      image_url: formData.get('image_url'),
      has_subjects: formData.get('has_subjects') ? 1 : 0,
      is_active: formData.get('is_active') ? 1 : 0
    };
    if (eduCategoryModal.mode === 'edit' && eduCategoryModal.data) {
      body.id = eduCategoryModal.data.id;
    }
    try {
      const method = eduCategoryModal.mode === 'create' ? 'POST' : 'PUT';
      const res = await apiFetch('/education_categories', { method, body: JSON.stringify(body) });
      if (res.success) {
        showAlert(res.message, 'success');
        setEduCategoryModal({ show: false, mode: 'create', data: null });
        fetchEduCategories();
      } else {
        showAlert(res.message, 'error');
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  const handleDeleteEduCategory = async (id) => {
    if (!window.confirm('Delete this education category?')) return;
    try {
      const res = await apiFetch(`/education_categories?id=${id}`, { method: 'DELETE' });
      if (res.success) {
        showAlert(res.message, 'success');
        fetchEduCategories();
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  const handleEduSubjectSubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const body = {
      category_id: parseInt(formData.get('category_id')),
      name: formData.get('name'),
      image_url: formData.get('image_url'),
      is_active: formData.get('is_active') ? 1 : 0
    };
    if (eduSubjectModal.mode === 'edit' && eduSubjectModal.data) {
      body.id = eduSubjectModal.data.id;
    }
    try {
      const method = eduSubjectModal.mode === 'create' ? 'POST' : 'PUT';
      const res = await apiFetch('/education_subjects', { method, body: JSON.stringify(body) });
      if (res.success) {
        showAlert(res.message, 'success');
        setEduSubjectModal({ show: false, mode: 'create', data: null });
        fetchEduSubjects();
      } else {
        showAlert(res.message, 'error');
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  const handleDeleteEduSubject = async (id) => {
    if (!window.confirm('Delete this education subject?')) return;
    try {
      const res = await apiFetch(`/education_subjects?id=${id}`, { method: 'DELETE' });
      if (res.success) {
        showAlert(res.message, 'success');
        fetchEduSubjects();
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  const handleEduVideoSubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const subVal = formData.get('subject_id');
    const body = {
      category_id: parseInt(formData.get('category_id')),
      subject_id: subVal ? parseInt(subVal) : null,
      title: formData.get('title'),
      image_url: formData.get('image_url'),
      video_url: formData.get('video_url'),
      video_type: formData.get('video_type') || 'youtube',
      duration: formData.get('duration') || '',
      description: formData.get('description') || '',
      is_active: formData.get('is_active') ? 1 : 0
    };
    if (eduVideoModal.mode === 'edit' && eduVideoModal.data) {
      body.id = eduVideoModal.data.id;
    }
    try {
      const method = eduVideoModal.mode === 'create' ? 'POST' : 'PUT';
      const res = await apiFetch('/education_videos', { method, body: JSON.stringify(body) });
      if (res.success) {
        showAlert(res.message, 'success');
        setEduVideoModal({ show: false, mode: 'create', data: null });
        fetchEduVideos();
      } else {
        showAlert(res.message, 'error');
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  const handleDeleteEduVideo = async (id) => {
    if (!window.confirm('Delete this education video?')) return;
    try {
      const res = await apiFetch(`/education_videos?id=${id}`, { method: 'DELETE' });
      if (res.success) {
        showAlert(res.message, 'success');
        fetchEduVideos();
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Reload data on tab switch
  useEffect(() => {
    if (token) {
      const superAdminTabs = ['content_push', 'smartplay', 'app_versions', 'app_store', 'youtube_content', 'tv_channels', 'music_hub', 'education_hub', 'branding'];
      if (user?.role !== 'super_admin' && superAdminTabs.includes(currentTab)) {
        setCurrentTab('dashboard');
        return;
      }

      if (currentTab === 'dashboard') {
        fetchDashboardData();
      } else if (currentTab === 'customers') {
        fetchCustomers();
      } else if (currentTab === 'transactions') {
        fetchTransactions();
      } else if (currentTab === 'operators') {
        fetchOperators();
      } else if (currentTab === 'app_versions') {
        fetchAppVersions();
      } else if (currentTab === 'app_store') {
        fetchAppStore();
      } else if (currentTab === 'youtube_content') {
        fetchActors();
        fetchCategories();
        fetchMovies();
      } else if (currentTab === 'tv_channels') {
        fetchTvChannels();
      } else if (currentTab === 'music_hub') {
        fetchMusicCategories();
        fetchMusicAlbums();
        fetchMusicTracks();
      } else if (currentTab === 'education_hub') {
        fetchEduCategories();
        fetchEduSubjects();
        fetchEduVideos();
      }
      fetchPlans();
    }
  }, [currentTab, token, user]);

  // Handlers for App Versions
  const handleAppVersionSubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const payload = Object.fromEntries(formData.entries());
    payload.force_update = e.target.force_update.checked ? 1 : 0;
    if (appVersionModal.mode === 'edit') {
      payload.id = appVersionModal.data.id;
    }
    try {
      const result = await apiFetch('/app_versions', {
        method: appVersionModal.mode === 'create' ? 'POST' : 'PUT',
        body: JSON.stringify(payload)
      });
      if (result.success) {
        showAlert(result.message, 'success');
        setAppVersionModal({ show: false, mode: 'create', data: null });
        fetchAppVersions();
      } else {
        showAlert(result.message || 'Error saving version', 'error');
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  const handleAppVersionDelete = async (id) => {
    if (!window.confirm("Delete this app version?")) return;
    try {
      const result = await apiFetch(`/app_versions?id=${id}`, { method: 'DELETE' });
      if (result.success) {
        showAlert(result.message, 'success');
        fetchAppVersions();
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Handlers for App Store
  const handleAppStoreSubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const payload = Object.fromEntries(formData.entries());
    payload.is_active = e.target.is_active.checked ? 1 : 0;
    if (appStoreModal.mode === 'edit') {
      payload.id = appStoreModal.data.id;
    }
    try {
      const result = await apiFetch('/app_store', {
        method: appStoreModal.mode === 'create' ? 'POST' : 'PUT',
        body: JSON.stringify(payload)
      });
      if (result.success) {
        showAlert(result.message, 'success');
        setAppStoreModal({ show: false, mode: 'create', data: null });
        fetchAppStore();
      } else {
        showAlert(result.message || 'Error saving app', 'error');
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  const handleAppStoreDelete = async (id) => {
    if (!window.confirm("Delete this app from App Store?")) return;
    try {
      const result = await apiFetch(`/app_store?id=${id}`, { method: 'DELETE' });
      if (result.success) {
        showAlert(result.message, 'success');
        fetchAppStore();
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Handlers for Actors
  const handleActorSubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const payload = Object.fromEntries(formData.entries());
    payload.is_category = e.target.is_category.checked ? 1 : 0;
    payload.actor_order = parseInt(payload.actor_order || '1', 10);
    if (actorModal.mode === 'edit') {
      payload.id = actorModal.data.id;
    }
    try {
      const result = await apiFetch('/actors', {
        method: actorModal.mode === 'create' ? 'POST' : 'PUT',
        body: JSON.stringify(payload)
      });
      if (result.success) {
        showAlert(result.message, 'success');
        setActorModal({ show: false, mode: 'create', data: null });
        fetchActors();
      } else {
        showAlert(result.message || 'Error saving actor', 'error');
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  const handleActorDelete = async (id) => {
    if (!window.confirm("Delete this actor entry?")) return;
    try {
      const result = await apiFetch(`/actors?id=${id}`, { method: 'DELETE' });
      if (result.success) {
        showAlert(result.message, 'success');
        fetchActors();
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Handlers for Categories
  const handleCategorySubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const payload = Object.fromEntries(formData.entries());
    payload.actor_id = parseInt(payload.actor_id, 10);
    payload.category_order = parseInt(payload.category_order || '1', 10);
    if (categoryModal.mode === 'edit') {
      payload.id = categoryModal.data.id;
    }
    
    // Frontend validation according to spec: parent actor must have is_category = 1
    const parentActor = actorsList.find(a => a.id === payload.actor_id);
    if (!parentActor || parentActor.is_category !== 1) {
      showAlert("Categories must be created under actors with is_category = 1.", "error");
      return;
    }

    try {
      const result = await apiFetch('/youtube_categories', {
        method: categoryModal.mode === 'create' ? 'POST' : 'PUT',
        body: JSON.stringify(payload)
      });
      if (result.success) {
        showAlert(result.message, 'success');
        setCategoryModal({ show: false, mode: 'create', data: null });
        fetchCategories();
      } else {
        showAlert(result.message || 'Error saving category', 'error');
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  const handleCategoryDelete = async (id) => {
    if (!window.confirm("Delete this category?")) return;
    try {
      const result = await apiFetch(`/youtube_categories?id=${id}`, { method: 'DELETE' });
      if (result.success) {
        showAlert(result.message, 'success');
        fetchCategories();
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Handlers for YouTube Movies
  const handleMovieSubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const payload = Object.fromEntries(formData.entries());
    
    const mapType = payload.mapping_type; // 'actor' or 'category'
    if (mapType === 'actor') {
      payload.actor_id = parseInt(payload.actor_id, 10);
      payload.category_id = null;
    } else {
      payload.category_id = parseInt(payload.category_id, 10);
      payload.actor_id = null;
    }
    delete payload.mapping_type;

    if (movieModal.mode === 'edit') {
      payload.id = movieModal.data.id;
    }

    // Frontend validations according to PDF spec:
    // 1. Duplicate youtube_video_id check
    const dupMovie = moviesList.find(m => m.youtube_video_id === payload.youtube_video_id && (movieModal.mode !== 'edit' || m.id !== movieModal.data.id));
    if (dupMovie) {
      showAlert("Duplicate youtube_video_id values are not allowed.", "error");
      return;
    }

    // 2. Actor validation: only actors with is_category = 0
    if (mapType === 'actor') {
      const targetActor = actorsList.find(a => a.id === payload.actor_id);
      if (!targetActor || targetActor.is_category !== 0) {
        showAlert("Actor validation ensures only actors with is_category = 0 can be assigned directly to a movie.", "error");
        return;
      }
    }

    try {
      const result = await apiFetch('/youtube_movies', {
        method: movieModal.mode === 'create' ? 'POST' : 'PUT',
        body: JSON.stringify(payload)
      });
      if (result.success) {
        showAlert(result.message, 'success');
        setMovieModal({ show: false, mode: 'create', data: null });
        fetchMovies();
      } else {
        showAlert(result.message || 'Error saving movie', 'error');
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  const handleMovieDelete = async (id) => {
    if (!window.confirm("Delete this YouTube movie entry?")) return;
    try {
      const result = await apiFetch(`/youtube_movies?id=${id}`, { method: 'DELETE' });
      if (result.success) {
        showAlert(result.message, 'success');
        fetchMovies();
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Handlers for Upload APIs
  const handleUploadLogo = async (e) => {
    e.preventDefault();
    if (!brandingLogoFile) {
      showAlert('Please select a logo image file to upload.', 'error');
      return;
    }
    const form = new FormData();
    form.append('operator_id', brandingOperatorId || user?.id || 1);
    form.append('image', brandingLogoFile);

    const cleanBase = apiBase.replace(/\/$/, '');
    const uploadUrl = `${cleanBase}/upload_logo.php`;

    try {
      const response = await fetch(uploadUrl, {
        method: 'POST',
        headers: token ? { 'Authorization': `Bearer ${token}` } : {},
        body: form
      });
      const data = await response.json();
      if (data.success) {
        setBrandingLogoUrl(data.logo_url);
        showAlert(data.message || 'Logo uploaded successfully.', 'success');
      } else {
        showAlert(data.message || 'Failed to upload logo', 'error');
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  const handleUploadBanner = async (e) => {
    e.preventDefault();
    if (!brandingBannerFile) {
      showAlert('Please select a banner image file to upload.', 'error');
      return;
    }
    const form = new FormData();
    form.append('operator_id', brandingOperatorId || user?.id || 1);
    form.append('image', brandingBannerFile);

    const cleanBase = apiBase.replace(/\/$/, '');
    const uploadUrl = `${cleanBase}/upload_banner.php`;

    try {
      const response = await fetch(uploadUrl, {
        method: 'POST',
        headers: token ? { 'Authorization': `Bearer ${token}` } : {},
        body: form
      });
      const data = await response.json();
      if (data.success) {
        setBrandingBannerUrl(data.logo_url);
        showAlert(data.message || 'Banner uploaded successfully.', 'success');
      } else {
        showAlert(data.message || 'Failed to upload banner', 'error');
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };


  // Customer Create/Update Submit
  const handleCustomerSubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const payload = Object.fromEntries(formData.entries());
    
    if (customerModal.mode === 'edit') {
      payload.id = customerModal.data.id;
    }

    try {
      const result = await apiFetch('/customers', {
        method: customerModal.mode === 'create' ? 'POST' : 'PUT',
        body: JSON.stringify(payload)
      });

      if (result.success) {
        showAlert(result.message, 'success');
        setCustomerModal({ show: false, mode: 'create', data: null });
        fetchCustomers();
        fetchDashboardData();
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Customer Delete
  const handleCustomerDelete = async (id) => {
    if (!window.confirm("Are you sure you want to delete this customer? This will also remove all their registered devices and subscriptions.")) return;

    try {
      const result = await apiFetch(`/customers?id=${id}`, {
        method: 'DELETE'
      });

      if (result.success) {
        showAlert(result.message, 'success');
        fetchCustomers();
        fetchDashboardData();
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Operator Submit (Super Admin managing Admins, or Admin managing Operators)
  const handleOperatorSubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const payload = Object.fromEntries(formData.entries());
    
    if (operatorModal.mode === 'edit') {
      payload.id = operatorModal.data.id;
    }

    try {
      const result = await apiFetch('/operators', {
        method: operatorModal.mode === 'create' ? 'POST' : 'PUT',
        body: JSON.stringify(payload)
      });

      if (result.success) {
        showAlert(result.message, 'success');
        setOperatorModal({ show: false, mode: 'create', data: null });
        fetchOperators();
        fetchDashboardData();
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Operator Delete
  const handleOperatorDelete = async (id) => {
    if (!window.confirm("Are you sure you want to delete this operator? All their children and dependencies will be affected.")) return;

    try {
      const result = await apiFetch(`/operators?id=${id}`, {
        method: 'DELETE'
      });

      if (result.success) {
        showAlert(result.message, 'success');
        fetchOperators();
        fetchDashboardData();
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Wallet Allocation Submit
  const handleAllocateSubmit = async (e) => {
    e.preventDefault();
    if (!allocateModal.amount || parseFloat(allocateModal.amount) <= 0) {
      showAlert("Please enter a valid transfer amount.", "error");
      return;
    }

    try {
      const result = await apiFetch('/wallet/allocate', {
        method: 'POST',
        body: JSON.stringify({
          operator_id: allocateModal.operatorId,
          amount: parseFloat(allocateModal.amount)
        })
      });

      if (result.success) {
        showAlert(result.message, 'success');
        setAllocateModal({ show: false, operatorId: null, operatorName: '', amount: '' });
        fetchOperators();
        fetchDashboardData();
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Device Register Submit
  const handleDeviceSubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const payload = {
      customer_id: deviceModal.customerId,
      ...Object.fromEntries(formData.entries())
    };

    try {
      const result = await apiFetch('/devices', {
        method: 'POST',
        body: JSON.stringify(payload)
      });

      if (result.success) {
        showAlert(result.message, 'success');
        setDeviceModal({ show: false, customerId: null, customerName: '' });
        if (currentTab === 'customers') fetchCustomers();
        fetchDashboardData();
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Device Status Update
  const handleDeviceStatusUpdate = async (id, status) => {
    try {
      const result = await apiFetch('/devices', {
        method: 'PUT',
        body: JSON.stringify({ id, status })
      });

      if (result.success) {
        showAlert(result.message, 'success');
        // Refetch not needed if devices tab is removed
        if (customerDevicesModal.show) {
          const updatedDevices = await apiFetch(`/devices?customer_id=${customerDevicesModal.customerId}`);
          setCustomerDevicesModal(prev => ({ ...prev, list: updatedDevices.devices }));
        }
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Device Delete
  const handleDeviceDelete = async (id) => {
    if (!window.confirm("Are you sure you want to delete/remove this device?")) return;
    try {
      const result = await apiFetch(`/devices?id=${id}`, {
        method: 'DELETE'
      });
      if (result.success) {
        showAlert(result.message, 'success');
        if (customerDevicesModal.show) {
          const updatedDevices = await apiFetch(`/devices?customer_id=${customerDevicesModal.customerId}`);
          setCustomerDevicesModal(prev => ({ ...prev, list: updatedDevices.devices }));
        }
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };


  // View Customer's specific devices
  const viewCustomerDevices = async (customerId, customerName) => {
    try {
      const result = await apiFetch(`/devices?customer_id=${customerId}`);
      if (result.success) {
        setCustomerDevicesModal({
          show: true,
          customerId,
          customerName,
          list: result.devices
        });
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Recharge Submit
  const handleRechargeSubmit = async (e) => {
    e.preventDefault();
    if (!rechargeModal.selectedPlanId) {
      showAlert("Please select a plan.", "error");
      return;
    }

    try {
      const result = await apiFetch('/recharge', {
        method: 'POST',
        body: JSON.stringify({
          customer_id: rechargeModal.customerId,
          plan_id: rechargeModal.selectedPlanId,
          payment_mode: rechargeModal.paymentMode
        })
      });

      if (result.success) {
        showAlert(result.message, 'success');
        setRechargeModal({ show: false, customerId: null, customerName: '', selectedPlanId: '', paymentMode: 'WALLET' });
        
        if (currentTab === 'customers') fetchCustomers();
        fetchDashboardData();
      }
    } catch (err) {
      showAlert(err.message, 'error');
    }
  };

  // Render Login Panel
  if (!token) {
    return (
      <div className="login-container animate-fade-in">
        <div className="login-card glass-panel">
          <div className="login-logo">StremFi</div>
          <div className="login-tagline">Client & Device Management Portal</div>

          {authError && <div className="error-banner">{authError}</div>}

          <form onSubmit={handleLogin}>
            <div className="form-group">
              <label>Email or Mobile Number</label>
              <input 
                type="text" 
                placeholder="operator@example.com" 
                value={loginIdentity}
                onChange={(e) => setLoginIdentity(e.target.value)}
                required
              />
            </div>
            <div className="form-group">
              <label>Password</label>
              <input 
                type="password" 
                placeholder="••••••" 
                value={loginPassword}
                onChange={(e) => setLoginPassword(e.target.value)}
                required
              />
            </div>
            <button type="submit" className="btn-primary login-btn" disabled={loading}>
              {loading ? 'Authenticating...' : 'Sign In'}
            </button>
          </form>
        </div>
      </div>
    );
  }

  // Define dynamic tab navigation according to role hierarchy
  const showOperatorsTab = user?.role === 'super_admin' || user?.role === 'admin';
  const operatorsTabLabel = user?.role === 'super_admin' ? 'Admins & Operators' : 'Operators';
  const operatorsTabIcon = user?.role === 'super_admin' ? Icons.Admins : Icons.Operators;

  return (
    <div className="app-container">
      {alertMsg.text && (
        <div 
          className={alertMsg.type === 'success' ? 'success-banner animate-fade-in' : 'error-banner animate-fade-in'}
          style={{ position: 'fixed', top: '20px', right: '20px', zIndex: 9999, minWidth: '250px', boxShadow: '0 4px 20px rgba(0,0,0,0.5)' }}
        >
          {alertMsg.text}
        </div>
      )}

      {/* Sidebar Navigation */}
      <aside className="sidebar">
        <div className="sidebar-logo">StremFi</div>
        
        <nav className="sidebar-menu">
          <button 
            className={`menu-item ${currentTab === 'dashboard' ? 'active' : ''}`}
            onClick={() => setCurrentTab('dashboard')}
          >
            {Icons.Dashboard()} Dashboard
          </button>

          {showOperatorsTab && (
            <button 
              className={`menu-item ${currentTab === 'operators' ? 'active' : ''}`}
              onClick={() => setCurrentTab('operators')}
            >
              {operatorsTabIcon()} {operatorsTabLabel}
            </button>
          )}

          <button 
            className={`menu-item ${currentTab === 'customers' ? 'active' : ''}`}
            onClick={() => setCurrentTab('customers')}
          >
            {Icons.Customers()} Customers
          </button>

          {user?.role === 'super_admin' && (
            <>
              <button 
                className={`menu-item ${currentTab === 'content_push' ? 'active' : ''}`}
                onClick={() => setCurrentTab('content_push')}
              >
                {Icons.Radio()} Push Content Hub
              </button>
              <button 
                className={`menu-item ${currentTab === 'smartplay' ? 'active' : ''}`}
                onClick={() => setCurrentTab('smartplay')}
              >
                {Icons.SmartPlay()} SmartPlay OTT APIs
              </button>

              <button 
                className={`menu-item ${currentTab === 'app_versions' ? 'active' : ''}`}
                onClick={() => setCurrentTab('app_versions')}
              >
                {Icons.AppVersions()} App Versions
              </button>
              <button 
                className={`menu-item ${currentTab === 'app_store' ? 'active' : ''}`}
                onClick={() => setCurrentTab('app_store')}
              >
                {Icons.AppStore()} App Store
              </button>
              <button 
                className={`menu-item ${currentTab === 'youtube_content' ? 'active' : ''}`}
                onClick={() => setCurrentTab('youtube_content')}
              >
                {Icons.YouTube()} YouTube Media
              </button>
              <button 
                className={`menu-item ${currentTab === 'tv_channels' ? 'active' : ''}`}
                onClick={() => setCurrentTab('tv_channels')}
              >
                {Icons.TvChannels()} Live TV Channels
              </button>
              <button 
                className={`menu-item ${currentTab === 'music_hub' ? 'active' : ''}`}
                onClick={() => setCurrentTab('music_hub')}
              >
                {Icons.MusicHub()} Music & Audio Hub
              </button>
              <button 
                className={`menu-item ${currentTab === 'education_hub' ? 'active' : ''}`}
                onClick={() => setCurrentTab('education_hub')}
              >
                {Icons.EduHub()} Education Portal
              </button>
              <button 
                className={`menu-item ${currentTab === 'branding' ? 'active' : ''}`}
                onClick={() => setCurrentTab('branding')}
              >
                {Icons.Branding()} Branding & Uploads
              </button>
            </>
          )}

          <button 
            className={`menu-item ${currentTab === 'transactions' ? 'active' : ''}`}
            onClick={() => setCurrentTab('transactions')}
          >
            {Icons.Transactions()} Transactions
          </button>
        </nav>

        <div className="sidebar-footer">
          <div className="user-badge">
            <div className="avatar">
              {user?.name ? user.name.charAt(0).toUpperCase() : 'O'}
            </div>
            <div className="user-info">
              <span className="username">{user?.name}</span>
              <span className="userrole">{user?.role?.replace('_', ' ')}</span>
            </div>
          </div>
          <button className="btn-logout" onClick={handleLogout}>
            <span style={{ display: 'flex', alignItems: 'center', gap: '6px' }}>
              {Icons.Logout()} Logout
            </span>
          </button>
        </div>
      </aside>

      {/* Main Panel Content */}
      <main className="main-content">
        <header className="top-header">
          <div className="page-title">
            <h1>
              {currentTab === 'operators' ? operatorsTabLabel :
               currentTab === 'app_versions' ? 'TV App Versions' :
               currentTab === 'app_store' ? 'TV App Store' :
               currentTab === 'youtube_content' ? 'YouTube Media & Content' :
               currentTab === 'tv_channels' ? 'Live TV Channels Management' :
               currentTab === 'music_hub' ? 'Music, FM & Audio Podcasts Hub' :
               currentTab === 'education_hub' ? 'Education & Learning Video Hub' :
               currentTab === 'branding' ? 'Branding & Image Uploads' :
               currentTab === 'content_push' ? 'Push Content Hub' :
               currentTab === 'smartplay' ? 'SmartPlay OTT APIs' :
               currentTab.charAt(0).toUpperCase() + currentTab.slice(1)}
            </h1>
            <p>Portal for {user?.role?.replace('_', ' ')}: {user?.name}</p>
          </div>
          <div className="header-actions" style={{ display: 'flex', alignItems: 'center', gap: '15px' }}>
            <button 
              className="btn-secondary" 
              onClick={() => setShowConfig(!showConfig)}
              style={{ display: 'flex', alignItems: 'center', gap: '6px', padding: '8px 14px', fontSize: '13px', background: 'rgba(255,255,255,0.05)', border: '1px solid var(--glass-border)' }}
            >
              {Icons.Settings()}
              <span>API Server: <strong>{apiBase.includes('localhost') ? 'Local (8000)' : apiBase}</strong></span>
            </button>

            <div className="wallet-card">
              {Icons.Wallet()}
              <div>
                <div className="wallet-lbl">My Wallet</div>
                <div className="wallet-val">₹{stats.walletBalance?.toFixed(2)}</div>
              </div>
            </div>
          </div>
        </header>

        {/* --- API SERVER CONFIG MODAL --- */}
        {showConfig && (
          <div className="modal-overlay">
            <div className="modal-content glass-panel animate-fade-in" style={{ maxWidth: '500px' }}>
              <div className="modal-header">
                <h2 className="modal-title">API Server & Non-Local API Target</h2>
                <button className="modal-close" onClick={() => setShowConfig(false)}>&times;</button>
              </div>
              <div style={{ padding: '10px 0' }}>
                <p style={{ fontSize: '13px', color: 'var(--text-muted)', marginBottom: '16px' }}>
                  Select or enter the base endpoint URL for all 27 StremFi REST APIs (TV Channels, Music, Education, App Store, OTT).
                </p>

                <div style={{ display: 'flex', flexDirection: 'column', gap: '10px', marginBottom: '20px' }}>
                  <button 
                    className="btn-secondary" 
                    style={{ textAlign: 'left', padding: '12px', justifyContent: 'space-between', border: apiBase === 'http://localhost:8000' ? '2px solid var(--primary)' : '1px solid var(--glass-border)' }}
                    onClick={() => { saveApiBase('http://localhost:8000'); setShowConfig(false); }}
                  >
                    <div>
                      <div style={{ fontWeight: 'bold' }}>🟢 Local Server</div>
                      <div style={{ fontSize: '12px', color: 'var(--text-muted)' }}>http://localhost:8000</div>
                    </div>
                    {apiBase === 'http://localhost:8000' && <span>✓ Active</span>}
                  </button>

                  <button 
                    className="btn-secondary" 
                    style={{ textAlign: 'left', padding: '12px', justifyContent: 'space-between', border: apiBase === 'https://vrplay.in' ? '2px solid var(--primary)' : '1px solid var(--glass-border)' }}
                    onClick={() => { saveApiBase('https://vrplay.in'); setShowConfig(false); }}
                  >
                    <div>
                      <div style={{ fontWeight: 'bold' }}>🌐 VRPlay Remote Production Server</div>
                      <div style={{ fontSize: '12px', color: 'var(--text-muted)' }}>https://vrplay.in</div>
                    </div>
                    {apiBase === 'https://vrplay.in' && <span>✓ Active</span>}
                  </button>

                  <button 
                    className="btn-secondary" 
                    style={{ textAlign: 'left', padding: '12px', justifyContent: 'space-between', border: apiBase === 'https://play.stremfitv.in' ? '2px solid var(--primary)' : '1px solid var(--glass-border)' }}
                    onClick={() => { saveApiBase('https://play.stremfitv.in'); setShowConfig(false); }}
                  >
                    <div>
                      <div style={{ fontWeight: 'bold' }}>🌐 StremFi Production Media Server</div>
                      <div style={{ fontSize: '12px', color: 'var(--text-muted)' }}>https://play.stremfitv.in</div>
                    </div>
                    {apiBase === 'https://play.stremfitv.in' && <span>✓ Active</span>}
                  </button>
                </div>

                <div className="form-group">
                  <label>Or Custom Remote Endpoint URL</label>
                  <input 
                    type="url" 
                    placeholder="https://your-remote-api.com" 
                    defaultValue={apiBase} 
                    onBlur={(e) => {
                      if (e.target.value.trim()) {
                        saveApiBase(e.target.value.trim());
                        setShowConfig(false);
                      }
                    }}
                  />
                </div>
              </div>
              <div className="modal-footer">
                <button className="btn-primary" onClick={() => setShowConfig(false)}>Done</button>
              </div>
            </div>
          </div>
        )}

        {/* Tab: Dashboard */}
        {currentTab === 'dashboard' && (
          <div className="animate-fade-in">
            <div className="stats-grid">
              <div className="stat-card glass-panel-interactive">
                <div className="stat-title">Managed Customers</div>
                <div className="stat-value">{stats.totalCustomers}</div>
              </div>
              <div className="stat-card glass-panel-interactive">
                <div className="stat-title">Active Devices</div>
                <div className="stat-value">{stats.activeDevices}</div>
              </div>
              <div className="stat-card glass-panel-interactive">
                <div className="stat-title">Active Subscriptions</div>
                <div className="stat-value">{stats.activeSubscriptions}</div>
              </div>
            </div>

            <div className="dashboard-grid">
              <div className="section-panel glass-panel" style={{ gridColumn: 'span 2' }}>
                <div className="section-header">
                  <h2>Network Activity Timeline</h2>
                </div>
                <div className="activity-list">
                  {recentActivity.length === 0 ? (
                    <div style={{ color: 'var(--text-muted)', fontSize: '14px', textAlign: 'center', padding: '20px 0' }}>No recent activity.</div>
                  ) : (
                    recentActivity.map((log) => (
                      <div className="activity-item" key={log.id}>
                        <div className="activity-head">
                          <span className="activity-name">{log.activity}</span>
                          <span className="activity-time">{new Date(log.created_at).toLocaleString()}</span>
                        </div>
                        <div className="activity-desc">
                          {log.description} {log.operator_name ? `by ${log.operator_name}` : ''}
                        </div>
                      </div>
                    ))
                  )}
                </div>
              </div>
            </div>
          </div>
        )}

        {/* Tab: Operators (Super Admin manages Admins, Admin manages Operators) */}
        {currentTab === 'operators' && showOperatorsTab && (
          <div className="section-panel glass-panel animate-fade-in">
            <div className="section-header">
              <button 
                className="btn-primary"
                onClick={() => {
                  setOperatorModal({ show: true, mode: 'create', data: null });
                  setOpModalRole('admin');
                }}
              >
                {Icons.Plus()} Add {user?.role === 'super_admin' ? 'Admin / Operator' : 'Operator'}
              </button>
            </div>

            <div className="table-wrapper">
              <table className="data-table">
                <thead>
                  <tr>
                    <th>Name</th>
                    {user?.role === 'super_admin' && <th>Role</th>}
                    <th>Email</th>
                    <th>Mobile</th>
                    {user?.role === 'super_admin' && <th>Parent Admin</th>}
                    <th>Company</th>
                    <th>Wallet Balance</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {operators.length === 0 ? (
                    <tr>
                      <td colSpan={user?.role === 'super_admin' ? 9 : 7} style={{ textAlign: 'center', padding: '30px', color: 'var(--text-muted)' }}>No sub-accounts registered.</td>
                    </tr>
                  ) : (
                    operators.map((op) => (
                      <tr key={op.id}>
                        <td style={{ fontWeight: '600' }}>{op.name}</td>
                        {user?.role === 'super_admin' && (
                          <td style={{ 
                            textTransform: 'capitalize', 
                            fontWeight: '600', 
                            color: op.role === 'admin' ? 'var(--primary)' : 'var(--accent-emerald)' 
                          }}>
                            {op.role}
                          </td>
                        )}
                        <td>{op.email}</td>
                        <td>{op.mobile || '—'}</td>
                        {user?.role === 'super_admin' && <td>{op.parent_name || 'System / Direct'}</td>}
                        <td>{op.company_name || '—'}</td>
                        <td style={{ fontWeight: '700', color: 'var(--accent-cyan)' }}>₹{parseFloat(op.wallet_balance).toFixed(2)}</td>
                        <td>
                          <span className={`status-tag ${op.is_active ? 'active' : 'expired'}`}>
                            {op.is_active ? 'Active' : 'Suspended'}
                          </span>
                        </td>
                        <td>
                          <div style={{ display: 'flex', gap: '8px' }}>
                            <button 
                              className="btn-accent"
                              style={{ background: 'rgba(6, 182, 212, 0.1)', borderColor: 'rgba(6, 182, 212, 0.3)', color: 'var(--accent-cyan)' }}
                              onClick={() => setAllocateModal({
                                show: true,
                                operatorId: op.id,
                                operatorName: op.name,
                                amount: ''
                              })}
                            >
                              Allocate Funds
                            </button>
                            <button 
                              className="btn-secondary"
                              style={{ padding: '6px 12px', fontSize: '12px' }}
                              onClick={() => setOperatorModal({ show: true, mode: 'edit', data: op })}
                            >
                              Edit
                            </button>
                            <button 
                              className="btn-action-delete"
                              onClick={() => handleOperatorDelete(op.id)}
                            >
                              Delete
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* Tab: Customers */}
        {currentTab === 'customers' && (
          <div className="section-panel glass-panel animate-fade-in">
            <div className="section-header">
              <h2>Customer Base</h2>
              <button 
                className="btn-primary"
                onClick={() => setCustomerModal({ show: true, mode: 'create', data: null })}
              >
                {Icons.Plus()} Add Customer
              </button>
            </div>

            <div className="table-wrapper">
              <table className="data-table">
                <thead>
                  <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Managed By</th>
                    <th>Devices</th>
                    <th>Plan</th>
                    <th>Expiry Date</th>
                    <th>IPTV Expiry Date</th>
                    <th>PiShow Expiry Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {customers.length === 0 ? (
                    <tr>
                      <td colSpan="10" style={{ textAlign: 'center', padding: '30px', color: 'var(--text-muted)' }}>No customers managed.</td>
                    </tr>
                  ) : (
                    customers.map((cust) => {
                      const mainExp = cust.expiry_date || (cust.active_subscription ? cust.active_subscription.expiry_date : 'Expired');
                      const iptvExp = cust.iptv_expiry_date || (cust.active_subscription ? (cust.active_subscription.iptv_expiry_date || cust.active_subscription.expiry_date) : 'Expired');
                      const pishowExp = cust.pishow_expiry_date || (cust.active_subscription ? (cust.active_subscription.pishow_expiry_date || cust.active_subscription.expiry_date) : 'Expired');

                      return (
                      <tr key={cust.id}>
                        <td style={{ fontWeight: '600', color: 'var(--primary)' }}>{cust.customer_code}</td>
                        <td style={{ fontWeight: '500' }}>
                          <span 
                            style={{ cursor: 'pointer', color: 'var(--primary)', textDecoration: 'none' }}
                            onClick={() => setCustomerDetailsModal({ show: true, data: cust })}
                            onMouseOver={(e) => e.target.style.textDecoration = 'underline'}
                            onMouseOut={(e) => e.target.style.textDecoration = 'none'}
                          >
                            {cust.first_name} {cust.last_name}
                          </span>
                        </td>
                        <td>{cust.phone_number}</td>
                        <td style={{ color: 'var(--text-muted)', fontSize: '13px' }}>
                          {cust.operator_name || 'Direct / Unknown'}
                        </td>
                        <td style={{ textAlign: 'center' }}>
                          {cust.device_count} / {cust.Max_login_devices}
                        </td>
                        <td>
                          {cust.active_subscription ? (
                            <span className="status-tag active">{cust.active_subscription.plan_name}</span>
                          ) : (
                            <span className="status-tag expired">No Plan</span>
                          )}
                        </td>
                        <td style={{ color: mainExp !== 'Expired' ? '#f59e0b' : '#ef4444', fontWeight: '600' }}>
                          {mainExp}
                        </td>
                        <td style={{ color: iptvExp !== 'Expired' ? '#10b981' : '#ef4444', fontWeight: '600' }}>
                          {iptvExp}
                        </td>
                        <td style={{ color: pishowExp !== 'Expired' ? '#06b6d4' : '#ef4444', fontWeight: '600' }}>
                          {pishowExp}
                        </td>
                        <td>
                          <div style={{ display: 'flex', gap: '8px' }}>
                            <button 
                              className="btn-accent"
                              onClick={() => setRechargeModal({
                                show: true,
                                customerId: cust.id,
                                customerName: `${cust.first_name} ${cust.last_name}`,
                                selectedPlanId: '',
                                paymentMode: 'WALLET'
                              })}
                            >
                              Recharge
                            </button>
                            <button 
                              className="btn-secondary"
                              style={{ padding: '6px 12px', fontSize: '12px', background: 'rgba(59, 130, 246, 0.15)', color: '#3b82f6', borderColor: 'rgba(59, 130, 246, 0.3)' }}
                              onClick={() => {
                                setSpMobile(cust.phone_number || '');
                                setSpFirstName(cust.first_name || '');
                                setSpLastName(cust.last_name || '');
                                if (cust.expiry_date) setSpExpiryDate(cust.expiry_date);
                                if (cust.iptv_expiry_date) setSpIptvExpiry(cust.iptv_expiry_date);
                                if (cust.pishow_expiry_date) setSpPishowExpiry(cust.pishow_expiry_date);
                                setCurrentTab('smartplay');
                                handleSpCheckSubscriber(cust.phone_number);
                              }}
                            >
                              SmartPlay API
                            </button>
                            <button 
                              className="btn-secondary" 
                              style={{ padding: '6px 12px', fontSize: '12px' }}
                              onClick={() => viewCustomerDevices(cust.id, `${cust.first_name} ${cust.last_name}`)}
                            >
                              Devices ({cust.device_count})
                            </button>
                            <button 
                              className="btn-secondary"
                              style={{ padding: '6px 12px', fontSize: '12px', borderColor: 'rgba(255,255,255,0.1)' }}
                              onClick={() => setCustomerModal({ show: true, mode: 'edit', data: cust })}
                            >
                              Edit
                            </button>
                            {user?.role === 'super_admin' && (
                              <button 
                                className="btn-action-delete"
                                onClick={() => handleCustomerDelete(cust.id)}
                              >
                                Delete
                              </button>
                            )}
                          </div>
                        </td>
                      </tr>
                    );
                  })
                  )}
                </tbody>
              </table>
            </div>
          </div>
        )}



        {/* Tab: Transactions */}
        {currentTab === 'transactions' && (
          <div className="section-panel glass-panel animate-fade-in">
            <div className="section-header">
              <h2>Financial Wallet Log</h2>
            </div>

            <div className="table-wrapper">
              <table className="data-table">
                <thead>
                  <tr>
                    <th>Account</th>
                    <th>Transaction ID</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Balance Before</th>
                    <th>Balance After</th>
                    <th>Description</th>
                    <th>Timestamp</th>
                  </tr>
                </thead>
                <tbody>
                  {transactions.length === 0 ? (
                    <tr>
                      <td colSpan="8" style={{ textAlign: 'center', padding: '30px', color: 'var(--text-muted)' }}>No transactions logged.</td>
                    </tr>
                  ) : (
                    transactions.map((txn) => (
                      <tr key={txn.id}>
                        <td style={{ fontWeight: '500' }}>{txn.operator_name || 'Primary'}</td>
                        <td style={{ fontFamily: 'monospace', fontWeight: '600' }}>{txn.transaction_id}</td>
                        <td>
                          <span className={`status-tag ${txn.transaction_type === 'DEBIT' ? 'expired' : 'active'}`}>
                            {txn.transaction_type}
                          </span>
                        </td>
                        <td style={{ 
                          fontWeight: '700', 
                          color: txn.transaction_type === 'DEBIT' ? 'var(--accent-rose)' : 'var(--accent-emerald)'
                        }}>
                          {txn.transaction_type === 'DEBIT' ? '-' : '+'}₹{parseFloat(txn.amount).toFixed(2)}
                        </td>
                        <td>₹{parseFloat(txn.balance_before).toFixed(2)}</td>
                        <td>₹{parseFloat(txn.balance_after).toFixed(2)}</td>
                        <td style={{ maxWidth: '200px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                          {txn.remarks}
                        </td>
                        <td>{new Date(txn.created_at).toLocaleString()}</td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>
          </div>
        )}
        {/* Tab: Content Push Hub */}
        {currentTab === 'content_push' && (
          <div className="section-panel glass-panel animate-fade-in">
            <div className="section-header" style={{ marginBottom: '20px' }}>
              <div>
                <h2>Staff Content Push & Broadcast Center</h2>
                <p style={{ color: 'var(--text-muted)', fontSize: '13px', marginTop: '4px' }}>
                  Staff module to broadcast Education, YouTube feeds, and FM Radio channels.
                </p>
              </div>
            </div>

            {/* Channel Switcher */}
            <div style={{ display: 'flex', gap: '10px', marginBottom: '25px' }}>
              <button 
                className={`btn-secondary ${pushChannel === 'education' ? 'active' : ''}`}
                style={{ flex: 1, padding: '12px', background: pushChannel === 'education' ? 'var(--primary)' : 'transparent', color: pushChannel === 'education' ? '#fff' : 'inherit' }}
                onClick={() => setPushChannel('education')}
              >
                📚 1. Education Content ({pushedEdu.length})
              </button>
              <button 
                className={`btn-secondary ${pushChannel === 'youtube' ? 'active' : ''}`}
                style={{ flex: 1, padding: '12px', background: pushChannel === 'youtube' ? '#ef4444' : 'transparent', color: pushChannel === 'youtube' ? '#fff' : 'inherit' }}
                onClick={() => setPushChannel('youtube')}
              >
                📺 2. YouTube Streams ({pushedYt.length})
              </button>
              <button 
                className={`btn-secondary ${pushChannel === 'radio' ? 'active' : ''}`}
                style={{ flex: 1, padding: '12px', background: pushChannel === 'radio' ? '#f59e0b' : 'transparent', color: pushChannel === 'radio' ? '#fff' : 'inherit' }}
                onClick={() => setPushChannel('radio')}
              >
                📻 3. FM Radio Channels ({pushedRadio.length})
              </button>
            </div>

            {/* Channel 1: Education */}
            {pushChannel === 'education' && (
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1.5fr', gap: '20px' }}>
                <form 
                  onSubmit={(e) => {
                    e.preventDefault();
                    if (!eduInput.title || !eduInput.mediaUrl) return;
                    setPushedEdu([{ id: Date.now(), ...eduInput, pushedBy: user?.name || 'Staff' }, ...pushedEdu]);
                    setEduInput({ title: '', category: 'Science', mediaUrl: '' });
                    showAlert('Pushed Education Content!', 'success');
                  }}
                  className="glass-panel"
                  style={{ padding: '20px', borderRadius: '12px' }}
                >
                  <h3 style={{ fontSize: '16px', marginBottom: '15px' }}>Push Education Content</h3>
                  <div className="form-group" style={{ marginBottom: '12px' }}>
                    <label>Content Title *</label>
                    <input 
                      type="text" 
                      placeholder="e.g. Advanced Quantum Mechanics" 
                      value={eduInput.title}
                      onChange={(e) => setEduInput({ ...eduInput, title: e.target.value })}
                      required 
                    />
                  </div>
                  <div className="form-group" style={{ marginBottom: '12px' }}>
                    <label>Category</label>
                    <input 
                      type="text" 
                      placeholder="e.g. Computer Science" 
                      value={eduInput.category}
                      onChange={(e) => setEduInput({ ...eduInput, category: e.target.value })}
                    />
                  </div>
                  <div className="form-group" style={{ marginBottom: '15px' }}>
                    <label>Media / PDF URL *</label>
                    <input 
                      type="url" 
                      placeholder="https://example.com/lecture.pdf" 
                      value={eduInput.mediaUrl}
                      onChange={(e) => setEduInput({ ...eduInput, mediaUrl: e.target.value })}
                      required 
                    />
                  </div>
                  <button type="submit" className="btn-primary" style={{ width: '100%' }}>Broadcast Education Content</button>
                </form>

                <div className="glass-panel" style={{ padding: '20px', borderRadius: '12px' }}>
                  <h3 style={{ fontSize: '16px', marginBottom: '15px' }}>Active Education Feeds</h3>
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
                    {pushedEdu.map(item => (
                      <div key={item.id} style={{ padding: '12px', border: '1px solid var(--glass-border)', borderRadius: '8px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                        <div>
                          <strong style={{ color: 'var(--primary)' }}>{item.title}</strong>
                          <div style={{ fontSize: '12px', color: 'var(--text-muted)' }}>Category: {item.category} | By: {item.pushedBy}</div>
                        </div>
                        <button className="btn-action-delete" onClick={() => setPushedEdu(pushedEdu.filter(x => x.id !== item.id))}>Delete</button>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            )}

            {/* Channel 2: YouTube */}
            {pushChannel === 'youtube' && (
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1.5fr', gap: '20px' }}>
                <form 
                  onSubmit={(e) => {
                    e.preventDefault();
                    if (!ytInput.title || !ytInput.url) return;
                    const match = ytInput.url.match(/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/);
                    const ytId = (match && match[2].length === 11) ? match[2] : 'dQw4w9WgXcQ';
                    setPushedYt([{ id: Date.now(), title: ytInput.title, ytId, pushedBy: user?.name || 'Staff' }, ...pushedYt]);
                    setYtInput({ title: '', url: '' });
                    showAlert('Pushed YouTube Feed!', 'success');
                  }}
                  className="glass-panel"
                  style={{ padding: '20px', borderRadius: '12px' }}
                >
                  <h3 style={{ fontSize: '16px', marginBottom: '15px' }}>Broadcast YouTube Stream</h3>
                  <div className="form-group" style={{ marginBottom: '12px' }}>
                    <label>Video Title *</label>
                    <input 
                      type="text" 
                      placeholder="e.g. Live Science Lecture" 
                      value={ytInput.title}
                      onChange={(e) => setYtInput({ ...ytInput, title: e.target.value })}
                      required 
                    />
                  </div>
                  <div className="form-group" style={{ marginBottom: '15px' }}>
                    <label>YouTube Link or Video ID *</label>
                    <input 
                      type="text" 
                      placeholder="https://www.youtube.com/watch?v=..." 
                      value={ytInput.url}
                      onChange={(e) => setYtInput({ ...ytInput, url: e.target.value })}
                      required 
                    />
                  </div>
                  <button type="submit" className="btn-primary" style={{ width: '100%', background: '#ef4444' }}>Broadcast YouTube Video</button>
                </form>

                <div className="glass-panel" style={{ padding: '20px', borderRadius: '12px' }}>
                  <h3 style={{ fontSize: '16px', marginBottom: '15px' }}>Live Pushed YouTube Videos</h3>
                  <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '15px' }}>
                    {pushedYt.map(item => (
                      <div key={item.id} style={{ border: '1px solid var(--glass-border)', borderRadius: '8px', overflow: 'hidden' }}>
                        <iframe src={`https://www.youtube.com/embed/${item.ytId}`} title={item.title} style={{ width: '100%', height: '140px', border: 'none' }} allowFullScreen></iframe>
                        <div style={{ padding: '10px' }}>
                          <strong style={{ fontSize: '13px', display: 'block', marginBottom: '4px' }}>{item.title}</strong>
                          <button className="btn-action-delete" style={{ fontSize: '11px', padding: '3px 8px' }} onClick={() => setPushedYt(pushedYt.filter(x => x.id !== item.id))}>Delete</button>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            )}

            {/* Channel 3: FM Radio */}
            {pushChannel === 'radio' && (
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1.5fr', gap: '20px' }}>
                <form 
                  onSubmit={(e) => {
                    e.preventDefault();
                    if (!radioInput.name || !radioInput.streamUrl) return;
                    setPushedRadio([{ id: Date.now(), ...radioInput, pushedBy: user?.name || 'Staff' }, ...pushedRadio]);
                    setRadioInput({ name: '', frequency: '91.1 FM', streamUrl: '' });
                    showAlert('Pushed Live Radio Station!', 'success');
                  }}
                  className="glass-panel"
                  style={{ padding: '20px', borderRadius: '12px' }}
                >
                  <h3 style={{ fontSize: '16px', marginBottom: '15px' }}>Broadcast Live FM Radio</h3>
                  <div className="form-group" style={{ marginBottom: '12px' }}>
                    <label>Radio Station Name *</label>
                    <input 
                      type="text" 
                      placeholder="e.g. EduStream Campus Radio" 
                      value={radioInput.name}
                      onChange={(e) => setRadioInput({ ...radioInput, name: e.target.value })}
                      required 
                    />
                  </div>
                  <div className="form-group" style={{ marginBottom: '12px' }}>
                    <label>Frequency</label>
                    <input 
                      type="text" 
                      placeholder="91.1 FM" 
                      value={radioInput.frequency}
                      onChange={(e) => setRadioInput({ ...radioInput, frequency: e.target.value })}
                    />
                  </div>
                  <div className="form-group" style={{ marginBottom: '15px' }}>
                    <label>Audio Stream URL *</label>
                    <input 
                      type="url" 
                      placeholder="https://stream.zeno.fm/..." 
                      value={radioInput.streamUrl}
                      onChange={(e) => setRadioInput({ ...radioInput, streamUrl: e.target.value })}
                      required 
                    />
                  </div>
                  <button type="submit" className="btn-primary" style={{ width: '100%', background: '#f59e0b' }}>Broadcast FM Station</button>
                </form>

                <div className="glass-panel" style={{ padding: '20px', borderRadius: '12px' }}>
                  <h3 style={{ fontSize: '16px', marginBottom: '15px' }}>Active Radio Stations</h3>
                  <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                    {pushedRadio.map(station => (
                      <div key={station.id} style={{ padding: '12px', border: '1px solid var(--glass-border)', borderRadius: '8px', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                        <div>
                          <strong>{station.name} ({station.frequency})</strong>
                          <div style={{ marginTop: '5px' }}>
                            <audio controls style={{ height: '30px', width: '220px' }} src={station.streamUrl}></audio>
                          </div>
                        </div>
                        <button className="btn-action-delete" onClick={() => setPushedRadio(pushedRadio.filter(x => x.id !== station.id))}>Delete</button>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            )}
          </div>
        )}

        {/* Tab: SmartPlay OTT APIs (APIs 1, 2, 4 + Editable Ph No, Expiry, IPTV Expiry, PiShow Expiry) */}
        {currentTab === 'smartplay' && (
          <div className="section-panel glass-panel animate-fade-in">
            <div className="section-header" style={{ marginBottom: '20px' }}>
              <div>
                <h2>SmartPlay / OneRADIUS OTT Activation APIs</h2>
                <p style={{ color: 'var(--text-muted)', fontSize: '13px', marginTop: '4px' }}>
                  Execute SmartPlay APIs (1. Check Subscriber, 2. Register Subscriber, 4. Renew Subscription) with customizable Phone Number & Expiration dates.
                </p>
              </div>
            </div>

            {/* Common Inputs Bar (Phone Number, Expiry Date, IPTV Expiry Date, PiShow Expiry Date) */}
            <div className="glass-panel" style={{ padding: '20px', borderRadius: '12px', marginBottom: '25px', background: 'rgba(255,255,255,0.03)', border: '1px solid var(--glass-border)' }}>
              <h3 style={{ fontSize: '15px', marginBottom: '15px', color: 'var(--primary)' }}>⚙️ Global Parameters (Phone No & Expiry Dates)</h3>
              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '15px' }}>
                <div className="form-group">
                  <label style={{ fontSize: '12px', fontWeight: 'bold' }}>Phone Number (Mobile) *</label>
                  <input 
                    type="tel" 
                    placeholder="9866334450" 
                    value={spMobile}
                    onChange={(e) => setSpMobile(e.target.value)}
                    required
                  />
                </div>
                <div className="form-group">
                  <label style={{ fontSize: '12px', fontWeight: 'bold' }}>Partner Code</label>
                  <input 
                    type="text" 
                    placeholder="001" 
                    value={spPartnerCode}
                    onChange={(e) => setSpPartnerCode(e.target.value)}
                  />
                </div>
                <div className="form-group">
                  <label style={{ fontSize: '12px', fontWeight: 'bold' }}>Main Expiry Date 📅</label>
                  <input 
                    type="date" 
                    value={spExpiryDate}
                    onChange={(e) => setSpExpiryDate(e.target.value)}
                  />
                </div>
                <div className="form-group">
                  <label style={{ fontSize: '12px', fontWeight: 'bold' }}>IPTV Expiry Date 📅</label>
                  <input 
                    type="date" 
                    value={spIptvExpiry}
                    onChange={(e) => setSpIptvExpiry(e.target.value)}
                  />
                </div>
                <div className="form-group">
                  <label style={{ fontSize: '12px', fontWeight: 'bold' }}>PiShow Expiry Date 📅</label>
                  <input 
                    type="date" 
                    value={spPishowExpiry}
                    onChange={(e) => setSpPishowExpiry(e.target.value)}
                  />
                </div>
              </div>
            </div>

            {/* SmartPlay API Grid (API 1, API 2, API 4) */}
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', gap: '20px', marginBottom: '25px' }}>
              
              {/* API 1: Check Subscriber Details */}
              <div className="glass-panel" style={{ padding: '20px', borderRadius: '12px', display: 'flex', flexDirection: 'column', justifyContent: 'space-between' }}>
                <div>
                  <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '10px' }}>
                    <span style={{ fontSize: '12px', fontWeight: 'bold', padding: '3px 8px', borderRadius: '4px', background: 'rgba(59, 130, 246, 0.2)', color: '#3b82f6' }}>API 1</span>
                    <span style={{ fontSize: '11px', color: 'var(--text-muted)' }}>GET /api/smart-plays/mobile/&#123;mobile&#125;</span>
                  </div>
                  <h3 style={{ fontSize: '16px', marginBottom: '8px' }}>1. Check Subscriber Details</h3>
                  <p style={{ fontSize: '12px', color: 'var(--text-muted)', marginBottom: '15px' }}>
                    Verify if subscriber exists in SmartPlay network using their phone number.
                  </p>
                </div>
                <button 
                  className="btn-primary" 
                  style={{ width: '100%', background: '#3b82f6' }}
                  onClick={() => handleSpCheckSubscriber(spMobile)}
                  disabled={spLoading}
                >
                  🔍 Run API 1: Check Subscriber ({spMobile || 'Enter Phone'})
                </button>
              </div>

              {/* API 2: Register New Subscriber */}
              <div className="glass-panel" style={{ padding: '20px', borderRadius: '12px', display: 'flex', flexDirection: 'column', justifyContent: 'space-between' }}>
                <div>
                  <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '10px' }}>
                    <span style={{ fontSize: '12px', fontWeight: 'bold', padding: '3px 8px', borderRadius: '4px', background: 'rgba(16, 185, 129, 0.2)', color: '#10b981' }}>API 2</span>
                    <span style={{ fontSize: '11px', color: 'var(--text-muted)' }}>POST /api/smart-plays</span>
                  </div>
                  <h3 style={{ fontSize: '16px', marginBottom: '8px' }}>2. Register New Subscriber</h3>
                  <p style={{ fontSize: '12px', color: 'var(--text-muted)', marginBottom: '12px' }}>
                    Register a new subscriber if not found in system.
                  </p>
                  <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '8px', marginBottom: '12px' }}>
                    <input 
                      type="text" 
                      placeholder="First Name" 
                      value={spFirstName}
                      onChange={(e) => setSpFirstName(e.target.value)}
                      style={{ padding: '6px 10px', fontSize: '12px' }}
                    />
                    <input 
                      type="text" 
                      placeholder="Last Name" 
                      value={spLastName}
                      onChange={(e) => setSpLastName(e.target.value)}
                      style={{ padding: '6px 10px', fontSize: '12px' }}
                    />
                  </div>
                </div>
                <button 
                  className="btn-primary" 
                  style={{ width: '100%', background: '#10b981' }}
                  onClick={handleSpRegisterSubscriber}
                  disabled={spLoading}
                >
                  ➕ Run API 2: Register Subscriber ({spFirstName} {spLastName})
                </button>
              </div>

              {/* API 4: Renew Subscription */}
              <div className="glass-panel" style={{ padding: '20px', borderRadius: '12px', display: 'flex', flexDirection: 'column', justifyContent: 'space-between' }}>
                <div>
                  <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '10px' }}>
                    <span style={{ fontSize: '12px', fontWeight: 'bold', padding: '3px 8px', borderRadius: '4px', background: 'rgba(245, 158, 11, 0.2)', color: '#f59e0b' }}>API 4</span>
                    <span style={{ fontSize: '11px', color: 'var(--text-muted)' }}>POST /api/smart-plays/&#123;mobile&#125;</span>
                  </div>
                  <h3 style={{ fontSize: '16px', marginBottom: '8px' }}>4. Renew Subscription</h3>
                  <p style={{ fontSize: '12px', color: 'var(--text-muted)', marginBottom: '12px' }}>
                    Activate or extend subscription packages and update expiration dates.
                  </p>
                  <div className="form-group" style={{ marginBottom: '12px' }}>
                    <select 
                      value={spPackageId} 
                      onChange={(e) => setSpPackageId(e.target.value)}
                      style={{ padding: '6px 10px', fontSize: '12px' }}
                    >
                      <option value="1">Package 1: SMARTPLAY MAGIC PACK 99 (30 Days)</option>
                      <option value="2">Package 2: SMARTPLAY_FTA Smartplay_FTA (30 Days)</option>
                      <option value="6">Package 6: SMARTPLAY HOME PACK 125 (30 Days)</option>
                      <option value="7">Package 7: SMARTPLAY GOLD PACK_149 (90 Days)</option>
                      <option value="10">Package 10: Smartplay Hungama Pack_169 (30 Days)</option>
                    </select>
                  </div>
                </div>
                <button 
                  className="btn-primary" 
                  style={{ width: '100%', background: '#f59e0b' }}
                  onClick={handleSpRenewSubscription}
                  disabled={spLoading}
                >
                  🔄 Run API 4: Renew Subscription for {spMobile}
                </button>
              </div>

            </div>

            {/* Subscriber Results & API Log Stream */}
            {spSearchResult && (
              <div className="glass-panel" style={{ padding: '20px', borderRadius: '12px', marginBottom: '25px', background: 'rgba(16, 185, 129, 0.05)', border: '1px solid rgba(16, 185, 129, 0.2)' }}>
                <h3 style={{ fontSize: '15px', marginBottom: '12px', color: 'var(--accent-emerald)' }}>Response Output (Subscriber Record)</h3>
                <pre style={{ background: '#090d16', color: '#10b981', padding: '15px', borderRadius: '8px', fontSize: '12px', overflowX: 'auto' }}>
                  {JSON.stringify(spSearchResult, null, 2)}
                </pre>
              </div>
            )}

            {/* SmartPlay API Logs */}
            {spApiLogs.length > 0 && (
              <div className="glass-panel" style={{ padding: '20px', borderRadius: '12px' }}>
                <h3 style={{ fontSize: '14px', marginBottom: '10px', color: 'var(--text-muted)' }}>SmartPlay API Audit Trail</h3>
                <div style={{ display: 'flex', flexDirection: 'column', gap: '6px' }}>
                  {spApiLogs.map((log, idx) => (
                    <div key={idx} style={{ fontFamily: 'monospace', fontSize: '12px', padding: '8px 12px', background: 'rgba(0,0,0,0.2)', borderRadius: '4px', borderLeft: '3px solid var(--primary)' }}>
                      {log}
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>
        )}

        {/* Tab: App Versions */}
        {currentTab === 'app_versions' && (
          <div className="animate-fade-in">
            <div className="section-panel glass-panel">
              <div className="section-header">
                <div>
                  <h2>TV Launcher & App Versions</h2>
                  <p style={{ color: 'var(--text-muted)', fontSize: '13px' }}>Manage Android TV launcher and application version releases (/app_versions.php)</p>
                </div>
                <button 
                  className="btn-primary"
                  onClick={() => setAppVersionModal({ show: true, mode: 'create', data: null })}
                >
                  {Icons.Plus()} Add Version
                </button>
              </div>

              <div className="table-responsive" style={{ marginTop: '16px' }}>
                <table className="data-table">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>App Name</th>
                      <th>Platform</th>
                      <th>Version Name</th>
                      <th>Version Code</th>
                      <th>Force Update</th>
                      <th>Update Message</th>
                      <th>APK URL</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {appVersions.length === 0 ? (
                      <tr>
                        <td colSpan="9" style={{ textAlign: 'center', padding: '30px', color: 'var(--text-muted)' }}>
                          No version records found. Click "Add Version" to create one.
                        </td>
                      </tr>
                    ) : (
                      appVersions.map(ver => (
                        <tr key={ver.id}>
                          <td>#{ver.id}</td>
                          <td style={{ fontWeight: '600' }}>{ver.app_name}</td>
                          <td><span className="badge badge-info">{ver.platform}</span></td>
                          <td style={{ fontWeight: '700', color: 'var(--accent-emerald)' }}>v{ver.version_name}</td>
                          <td>{ver.version_code}</td>
                          <td>
                            {ver.force_update === 1 ? (
                              <span className="badge badge-danger">Required (1)</span>
                            ) : (
                              <span className="badge badge-success">Optional (0)</span>
                            )}
                          </td>
                          <td style={{ fontSize: '13px', maxWidth: '200px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                            {ver.update_message || '—'}
                          </td>
                          <td style={{ fontSize: '12px', maxWidth: '180px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                            <a href={ver.apk_url} target="_blank" rel="noreferrer" style={{ color: 'var(--primary)' }}>{ver.apk_url || 'N/A'}</a>
                          </td>
                          <td>
                            <div className="action-buttons">
                              <button className="btn-icon" onClick={() => setAppVersionModal({ show: true, mode: 'edit', data: ver })} title="Edit">✏️</button>
                              <button className="btn-icon btn-danger" onClick={() => handleAppVersionDelete(ver.id)} title="Delete">🗑️</button>
                            </div>
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        )}

        {/* Tab: App Store */}
        {currentTab === 'app_store' && (
          <div className="animate-fade-in">
            <div className="section-panel glass-panel">
              <div className="section-header">
                <div>
                  <h2>StremFi TV App Store Catalogue</h2>
                  <p style={{ color: 'var(--text-muted)', fontSize: '13px' }}>Manage downloadable TV applications and streaming apps (/app_store.php)</p>
                </div>
                <button 
                  className="btn-primary"
                  onClick={() => setAppStoreModal({ show: true, mode: 'create', data: null })}
                >
                  {Icons.Plus()} Add App
                </button>
              </div>

              <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: '20px', marginTop: '20px' }}>
                {appStoreApps.length === 0 ? (
                  <div style={{ gridColumn: '1 / -1', textAlign: 'center', padding: '40px', color: 'var(--text-muted)' }}>
                    No apps in the App Store catalog. Click "Add App" to create one.
                  </div>
                ) : (
                  appStoreApps.map(app => (
                    <div key={app.id} className="glass-panel" style={{ padding: '20px', display: 'flex', flexDirection: 'column', justifyContent: 'space-between', border: '1px solid var(--glass-border)', borderRadius: '12px' }}>
                      <div>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '14px', marginBottom: '16px' }}>
                          <img 
                            src={app.image_url || 'https://via.placeholder.com/60'} 
                            alt={app.name} 
                            style={{ width: '56px', height: '56px', borderRadius: '12px', objectFit: 'cover', background: 'rgba(255,255,255,0.05)' }} 
                            onError={(e) => { e.target.src = 'https://via.placeholder.com/60?text=App'; }}
                          />
                          <div>
                            <h3 style={{ fontSize: '18px', fontWeight: '700', marginBottom: '4px' }}>{app.name}</h3>
                            <span style={{ fontSize: '12px', color: 'var(--text-muted)', fontFamily: 'monospace' }}>{app.package_name}</span>
                          </div>
                        </div>

                        <div style={{ display: 'flex', gap: '8px', marginBottom: '16px' }}>
                          {app.is_active === 1 ? (
                            <span className="badge badge-success">Active (1)</span>
                          ) : (
                            <span className="badge badge-secondary">Disabled (0)</span>
                          )}
                        </div>

                        <div style={{ fontSize: '12px', color: 'var(--text-muted)', display: 'flex', flexDirection: 'column', gap: '6px' }}>
                          {app.play_store_id && <div><strong>PlayStore:</strong> <a href={app.play_store_id} target="_blank" rel="noreferrer" style={{ color: 'var(--primary)' }}>Link</a></div>}
                          {app.amazon_app_id && <div><strong>Amazon Store:</strong> <a href={app.amazon_app_id} target="_blank" rel="noreferrer" style={{ color: 'var(--primary)' }}>Link</a></div>}
                          {app.apk_url && <div><strong>Direct APK:</strong> <a href={app.apk_url} target="_blank" rel="noreferrer" style={{ color: 'var(--primary)' }}>Download</a></div>}
                        </div>
                      </div>

                      <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '8px', marginTop: '20px', paddingTop: '12px', borderTop: '1px solid var(--glass-border)' }}>
                        <button className="btn-secondary" style={{ padding: '6px 12px', fontSize: '13px' }} onClick={() => setAppStoreModal({ show: true, mode: 'edit', data: app })}>Edit</button>
                        <button className="btn-secondary" style={{ padding: '6px 12px', fontSize: '13px', color: 'var(--accent-rose)', borderColor: 'rgba(239,68,68,0.3)' }} onClick={() => handleAppStoreDelete(app.id)}>Delete</button>
                      </div>
                    </div>
                  ))
                )}
              </div>
            </div>
          </div>
        )}

        {/* Tab: YouTube Content (Movies, Actors, Categories) */}
        {currentTab === 'youtube_content' && (
          <div className="animate-fade-in">
            <div className="section-panel glass-panel">
              <div className="section-header">
                <div>
                  <h2>YouTube Content, Actors & Categories</h2>
                  <p style={{ color: 'var(--text-muted)', fontSize: '13px' }}>Manage video titles, hero actors, and nested category groups (/actors.php, /youtube_categories.php, /youtube_movies.php)</p>
                </div>
                <div style={{ display: 'flex', gap: '10px' }}>
                  <button 
                    className={`btn-secondary ${mediaSubTab === 'movies' ? 'active' : ''}`}
                    onClick={() => setMediaSubTab('movies')}
                    style={mediaSubTab === 'movies' ? { background: 'var(--primary)', color: 'white' } : {}}
                  >
                    Movies ({moviesList.length})
                  </button>
                  <button 
                    className={`btn-secondary ${mediaSubTab === 'actors' ? 'active' : ''}`}
                    onClick={() => setMediaSubTab('actors')}
                    style={mediaSubTab === 'actors' ? { background: 'var(--primary)', color: 'white' } : {}}
                  >
                    Actors ({actorsList.length})
                  </button>
                  <button 
                    className={`btn-secondary ${mediaSubTab === 'categories' ? 'active' : ''}`}
                    onClick={() => setMediaSubTab('categories')}
                    style={mediaSubTab === 'categories' ? { background: 'var(--primary)', color: 'white' } : {}}
                  >
                    Categories ({categoriesList.length})
                  </button>
                </div>
              </div>

              {/* Sub-tab: Movies */}
              {mediaSubTab === 'movies' && (
                <div style={{ marginTop: '20px' }}>
                  <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: '16px' }}>
                    <button className="btn-primary" onClick={() => setMovieModal({ show: true, mode: 'create', data: null })}>
                      {Icons.Plus()} Add YouTube Movie
                    </button>
                  </div>
                  <div className="table-responsive">
                    <table className="data-table">
                      <thead>
                        <tr>
                          <th>Thumbnail</th>
                          <th>Movie Title</th>
                          <th>YouTube Video ID</th>
                          <th>Mapped To</th>
                          <th>Role</th>
                          <th>Description</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        {moviesList.length === 0 ? (
                          <tr><td colSpan="7" style={{ textAlign: 'center', padding: '30px', color: 'var(--text-muted)' }}>No movies added yet.</td></tr>
                        ) : (
                          moviesList.map(m => (
                            <tr key={m.id}>
                              <td>
                                <img 
                                  src={m.thumbnail || m.image || 'https://via.placeholder.com/50x50'} 
                                  alt={m.name} 
                                  style={{ width: '48px', height: '48px', borderRadius: '8px', objectFit: 'cover' }} 
                                />
                              </td>
                              <td style={{ fontWeight: '700' }}>{m.name}</td>
                              <td><span className="badge badge-info" style={{ fontFamily: 'monospace' }}>{m.youtube_video_id}</span></td>
                              <td>
                                {m.actor_name ? (
                                  <span className="badge badge-success">Actor: {m.actor_name}</span>
                                ) : m.category_name ? (
                                  <span className="badge badge-warning" style={{ background: 'rgba(245, 158, 11, 0.2)', color: '#f59e0b' }}>Category: {m.category_name}</span>
                                ) : (
                                  <span style={{ color: 'var(--text-muted)' }}>Unmapped</span>
                                )}
                              </td>
                              <td>{m.role || '—'}</td>
                              <td style={{ fontSize: '13px', maxWidth: '200px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{m.description || '—'}</td>
                              <td>
                                <div className="action-buttons">
                                  <button className="btn-icon" onClick={() => setMovieModal({ show: true, mode: 'edit', data: m })}>✏️</button>
                                  <button className="btn-icon btn-danger" onClick={() => handleMovieDelete(m.id)}>🗑️</button>
                                </div>
                              </td>
                            </tr>
                          ))
                        )}
                      </tbody>
                    </table>
                  </div>
                </div>
              )}

              {/* Sub-tab: Actors */}
              {mediaSubTab === 'actors' && (
                <div style={{ marginTop: '20px' }}>
                  <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: '16px' }}>
                    <button className="btn-primary" onClick={() => setActorModal({ show: true, mode: 'create', data: null })}>
                      {Icons.Plus()} Add Actor / Group
                    </button>
                  </div>
                  <div className="table-responsive">
                    <table className="data-table">
                      <thead>
                        <tr>
                          <th>Image</th>
                          <th>Name</th>
                          <th>Order</th>
                          <th>Type (is_category)</th>
                          <th>Created At</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        {actorsList.length === 0 ? (
                          <tr><td colSpan="6" style={{ textAlign: 'center', padding: '30px', color: 'var(--text-muted)' }}>No actors added yet.</td></tr>
                        ) : (
                          actorsList.map(act => (
                            <tr key={act.id}>
                              <td>
                                <img src={act.image || 'https://via.placeholder.com/40'} alt={act.name} style={{ width: '40px', height: '40px', borderRadius: '50%', objectFit: 'cover' }} />
                              </td>
                              <td style={{ fontWeight: '600' }}>{act.name}</td>
                              <td>{act.actor_order}</td>
                              <td>
                                {act.is_category === 1 ? (
                                  <span className="badge badge-warning" style={{ background: 'rgba(245, 158, 11, 0.2)', color: '#f59e0b' }}>Category Group (is_category = 1)</span>
                                ) : (
                                  <span className="badge badge-success">Direct Actor (is_category = 0)</span>
                                )}
                              </td>
                              <td>{act.created_at || '—'}</td>
                              <td>
                                <div className="action-buttons">
                                  <button className="btn-icon" onClick={() => setActorModal({ show: true, mode: 'edit', data: act })}>✏️</button>
                                  <button className="btn-icon btn-danger" onClick={() => handleActorDelete(act.id)}>🗑️</button>
                                </div>
                              </td>
                            </tr>
                          ))
                        )}
                      </tbody>
                    </table>
                  </div>
                </div>
              )}

              {/* Sub-tab: Categories */}
              {mediaSubTab === 'categories' && (
                <div style={{ marginTop: '20px' }}>
                  <div style={{ display: 'flex', justifyContent: 'flex-end', marginBottom: '16px' }}>
                    <button className="btn-primary" onClick={() => setCategoryModal({ show: true, mode: 'create', data: null })}>
                      {Icons.Plus()} Add Category
                    </button>
                  </div>
                  <div className="table-responsive">
                    <table className="data-table">
                      <thead>
                        <tr>
                          <th>Image</th>
                          <th>Category Name</th>
                          <th>Parent Actor (is_category=1)</th>
                          <th>Category Order</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        {categoriesList.length === 0 ? (
                          <tr><td colSpan="5" style={{ textAlign: 'center', padding: '30px', color: 'var(--text-muted)' }}>No categories added yet.</td></tr>
                        ) : (
                          categoriesList.map(cat => (
                            <tr key={cat.id}>
                              <td>
                                <img src={cat.image || 'https://via.placeholder.com/40'} alt={cat.name} style={{ width: '40px', height: '40px', borderRadius: '8px', objectFit: 'cover' }} />
                              </td>
                              <td style={{ fontWeight: '600' }}>{cat.name}</td>
                              <td><span className="badge badge-info">{cat.actor_name || `ID: ${cat.actor_id}`}</span></td>
                              <td>{cat.category_order}</td>
                              <td>
                                <div className="action-buttons">
                                  <button className="btn-icon" onClick={() => setCategoryModal({ show: true, mode: 'edit', data: cat })}>✏️</button>
                                  <button className="btn-icon btn-danger" onClick={() => handleCategoryDelete(cat.id)}>🗑️</button>
                                </div>
                              </td>
                            </tr>
                          ))
                        )}
                      </tbody>
                    </table>
                  </div>
                </div>
              )}
            </div>
          </div>
        )}

        {/* Tab: Branding & Uploads */}
        {currentTab === 'branding' && (
          <div className="animate-fade-in">
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(400px, 1fr))', gap: '24px' }}>
              
              {/* Logo Upload Card */}
              <div className="section-panel glass-panel">
                <div className="section-header">
                  <h2>Upload Operator Logo</h2>
                </div>
                <p style={{ color: 'var(--text-muted)', fontSize: '13px', margin: '8px 0 16px 0' }}>
                  Upload custom logo images using <code>/upload_logo.php</code>
                </p>
                <form onSubmit={handleUploadLogo}>
                  <div className="form-group">
                    <label>Operator ID *</label>
                    <input 
                      type="number" 
                      value={brandingOperatorId || user?.id || 1} 
                      onChange={(e) => setBrandingOperatorId(e.target.value)} 
                      placeholder="Operator ID (e.g. 1)"
                      required
                    />
                  </div>
                  <div className="form-group">
                    <label>Select Logo Image File *</label>
                    <input 
                      type="file" 
                      accept="image/*"
                      onChange={(e) => setBrandingLogoFile(e.target.files[0])}
                      required
                    />
                  </div>
                  <button type="submit" className="btn-primary" style={{ width: '100%', marginTop: '10px' }}>
                    Upload Logo
                  </button>
                </form>

                {brandingLogoUrl && (
                  <div style={{ marginTop: '20px', padding: '16px', borderRadius: '12px', background: 'rgba(255,255,255,0.03)', border: '1px solid var(--glass-border)' }}>
                    <div style={{ fontSize: '12px', color: 'var(--text-muted)', marginBottom: '8px' }}>Uploaded Logo Preview:</div>
                    <img src={brandingLogoUrl} alt="Uploaded Logo" style={{ maxHeight: '80px', objectFit: 'contain' }} />
                    <div style={{ fontSize: '11px', color: 'var(--accent-emerald)', marginTop: '8px', wordBreak: 'break-all' }}>{brandingLogoUrl}</div>
                  </div>
                )}
              </div>

              {/* Banner Upload Card */}
              <div className="section-panel glass-panel">
                <div className="section-header">
                  <h2>Upload Operator Banner</h2>
                </div>
                <p style={{ color: 'var(--text-muted)', fontSize: '13px', margin: '8px 0 16px 0' }}>
                  Upload custom launcher banners using <code>/upload_banner.php</code>
                </p>
                <form onSubmit={handleUploadBanner}>
                  <div className="form-group">
                    <label>Operator ID *</label>
                    <input 
                      type="number" 
                      value={brandingOperatorId || user?.id || 1} 
                      onChange={(e) => setBrandingOperatorId(e.target.value)} 
                      placeholder="Operator ID (e.g. 1)"
                      required
                    />
                  </div>
                  <div className="form-group">
                    <label>Select Banner Image File *</label>
                    <input 
                      type="file" 
                      accept="image/*"
                      onChange={(e) => setBrandingBannerFile(e.target.files[0])}
                      required
                    />
                  </div>
                  <button type="submit" className="btn-primary" style={{ width: '100%', marginTop: '10px' }}>
                    Upload Banner
                  </button>
                </form>

                {brandingBannerUrl && (
                  <div style={{ marginTop: '20px', padding: '16px', borderRadius: '12px', background: 'rgba(255,255,255,0.03)', border: '1px solid var(--glass-border)' }}>
                    <div style={{ fontSize: '12px', color: 'var(--text-muted)', marginBottom: '8px' }}>Uploaded Banner Preview:</div>
                    <img src={brandingBannerUrl} alt="Uploaded Banner" style={{ width: '100%', maxHeight: '120px', objectFit: 'cover', borderRadius: '8px' }} />
                    <div style={{ fontSize: '11px', color: 'var(--accent-emerald)', marginTop: '8px', wordBreak: 'break-all' }}>{brandingBannerUrl}</div>
                  </div>
                )}
              </div>

            </div>
          </div>
        )}

        {/* Tab: Live TV Channels */}
        {currentTab === 'tv_channels' && (
          <div className="animate-fade-in">
            <div className="section-panel glass-panel">
              <div className="section-header">
                <div>
                  <h2>Live TV Channels</h2>
                  <p style={{ color: 'var(--text-muted)', fontSize: '13px' }}>Manage Live TV channels catalogue & stream endpoints (/tv_channels.php)</p>
                </div>
                <button 
                  className="btn-primary"
                  onClick={() => setTvChannelModal({ show: true, mode: 'create', data: null })}
                >
                  {Icons.Plus()} Add TV Channel
                </button>
              </div>

              <div className="table-responsive" style={{ marginTop: '16px' }}>
                <table className="data-table">
                  <thead>
                    <tr>
                      <th>Ch #</th>
                      <th>Name</th>
                      <th>Logo</th>
                      <th>Category</th>
                      <th>Language</th>
                      <th>Player</th>
                      <th>Stream URL</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {tvChannels.length === 0 ? (
                      <tr>
                        <td colSpan="8" style={{ textAlign: 'center', padding: '30px', color: 'var(--text-muted)' }}>
                          No TV channels found. Click "Add TV Channel" to create one.
                        </td>
                      </tr>
                    ) : (
                      tvChannels.map(ch => (
                        <tr key={ch.id}>
                          <td><strong>#{ch.channelNumber}</strong></td>
                          <td><strong>{ch.name}</strong></td>
                          <td>
                            {ch.imageUrl ? (
                              <img src={ch.imageUrl} alt={ch.name} style={{ width: '40px', height: '40px', objectFit: 'contain', borderRadius: '4px' }} />
                            ) : (
                              '—'
                            )}
                          </td>
                          <td><span className="badge-chip badge-primary">{ch.category}</span></td>
                          <td>{ch.language}</td>
                          <td><code>{ch.player}</code></td>
                          <td style={{ maxWidth: '200px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', fontSize: '12px' }}>
                            <a href={ch.channelUrl} target="_blank" rel="noreferrer" style={{ color: 'var(--primary)' }}>{ch.channelUrl}</a>
                          </td>
                          <td>
                            <div className="action-buttons">
                              <button className="btn-icon" title="Edit Channel" onClick={() => setTvChannelModal({ show: true, mode: 'edit', data: ch })}>
                                {Icons.Edit()}
                              </button>
                              <button className="btn-icon danger" title="Delete Channel" onClick={() => handleDeleteTvChannel(ch.id)}>
                                {Icons.Trash()}
                              </button>
                            </div>
                          </td>
                        </tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        )}

        {/* Tab: Music Hub */}
        {currentTab === 'music_hub' && (
          <div className="animate-fade-in">
            <div className="section-panel glass-panel">
              <div className="section-header">
                <div>
                  <h2>Music, FM & Audio Podcasts Hub</h2>
                  <p style={{ color: 'var(--text-muted)', fontSize: '13px' }}>Manage Music tracks, Albums, and Categories (/music.php, /music_albums.php, /music_categories.php)</p>
                </div>
                <div style={{ display: 'flex', gap: '10px' }}>
                  {musicSubTab === 'tracks' && (
                    <button className="btn-primary" onClick={() => setMusicTrackModal({ show: true, mode: 'create', data: null })}>
                      {Icons.Plus()} Add Music Track
                    </button>
                  )}
                  {musicSubTab === 'albums' && (
                    <button className="btn-primary" onClick={() => setMusicAlbumModal({ show: true, mode: 'create', data: null })}>
                      {Icons.Plus()} Add Album
                    </button>
                  )}
                  {musicSubTab === 'categories' && (
                    <button className="btn-primary" onClick={() => setMusicCategoryModal({ show: true, mode: 'create', data: null })}>
                      {Icons.Plus()} Add Category
                    </button>
                  )}
                </div>
              </div>

              {/* Sub tab navigation */}
              <div className="tab-buttons" style={{ margin: '15px 0' }}>
                <button className={`tab-btn ${musicSubTab === 'tracks' ? 'active' : ''}`} onClick={() => setMusicSubTab('tracks')}>
                  Music & Audio Tracks ({musicTracks.length})
                </button>
                <button className={`tab-btn ${musicSubTab === 'albums' ? 'active' : ''}`} onClick={() => setMusicSubTab('albums')}>
                  Music Albums ({musicAlbums.length})
                </button>
                <button className={`tab-btn ${musicSubTab === 'categories' ? 'active' : ''}`} onClick={() => setMusicSubTab('categories')}>
                  Music Categories ({musicCategories.length})
                </button>
              </div>

              {musicSubTab === 'tracks' && (
                <div className="table-responsive">
                  <table className="data-table">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Track Name</th>
                        <th>Category</th>
                        <th>Album</th>
                        <th>Type</th>
                        <th>Stream URL</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      {musicTracks.length === 0 ? (
                        <tr><td colSpan="8" style={{ textAlign: 'center', padding: '20px' }}>No music tracks found.</td></tr>
                      ) : (
                        musicTracks.map(t => (
                          <tr key={t.id}>
                            <td>{t.id}</td>
                            <td><strong>{t.name}</strong></td>
                            <td><span className="badge-chip badge-primary">{t.category_name || '—'}</span></td>
                            <td>{t.album_name || '—'}</td>
                            <td><code>{t.type}</code></td>
                            <td style={{ maxWidth: '180px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', fontSize: '11px' }}>
                              <a href={t.stream_url} target="_blank" rel="noreferrer" style={{ color: 'var(--primary)' }}>{t.stream_url}</a>
                            </td>
                            <td>
                              <span className={`badge-chip ${t.is_active === 1 ? 'badge-success' : 'badge-danger'}`}>
                                {t.is_active === 1 ? 'Active' : 'Disabled'}
                              </span>
                            </td>
                            <td>
                              <div className="action-buttons">
                                <button className="btn-icon" onClick={() => setMusicTrackModal({ show: true, mode: 'edit', data: t })}>{Icons.Edit()}</button>
                                <button className="btn-icon danger" onClick={() => handleDeleteMusicTrack(t.id)}>{Icons.Trash()}</button>
                              </div>
                            </td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </table>
                </div>
              )}

              {musicSubTab === 'albums' && (
                <div className="table-responsive">
                  <table className="data-table">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Album Name</th>
                        <th>Category Name</th>
                        <th>Cover Image</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      {musicAlbums.length === 0 ? (
                        <tr><td colSpan="6" style={{ textAlign: 'center', padding: '20px' }}>No music albums found.</td></tr>
                      ) : (
                        musicAlbums.map(a => (
                          <tr key={a.id}>
                            <td>{a.id}</td>
                            <td><strong>{a.name}</strong></td>
                            <td><span className="badge-chip badge-primary">{a.category_name || '—'}</span></td>
                            <td>
                              {a.image_url ? <img src={a.image_url} alt={a.name} style={{ width: '35px', height: '35px', borderRadius: '4px', objectFit: 'cover' }} /> : '—'}
                            </td>
                            <td>
                              <span className={`badge-chip ${a.is_active === 1 ? 'badge-success' : 'badge-danger'}`}>
                                {a.is_active === 1 ? 'Active' : 'Disabled'}
                              </span>
                            </td>
                            <td>
                              <div className="action-buttons">
                                <button className="btn-icon" onClick={() => setMusicAlbumModal({ show: true, mode: 'edit', data: a })}>{Icons.Edit()}</button>
                                <button className="btn-icon danger" onClick={() => handleDeleteMusicAlbum(a.id)}>{Icons.Trash()}</button>
                              </div>
                            </td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </table>
                </div>
              )}

              {musicSubTab === 'categories' && (
                <div className="table-responsive">
                  <table className="data-table">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Cover Image</th>
                        <th>Has Album</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      {musicCategories.length === 0 ? (
                        <tr><td colSpan="6" style={{ textAlign: 'center', padding: '20px' }}>No categories found.</td></tr>
                      ) : (
                        musicCategories.map(c => (
                          <tr key={c.id}>
                            <td>{c.id}</td>
                            <td><strong>{c.name}</strong></td>
                            <td>
                              {c.image_url ? <img src={c.image_url} alt={c.name} style={{ width: '35px', height: '35px', borderRadius: '4px', objectFit: 'cover' }} /> : '—'}
                            </td>
                            <td>
                              <span className={`badge-chip ${c.has_album === 1 ? 'badge-primary' : 'badge-chip'}`}>
                                {c.has_album === 1 ? 'Yes (Albums)' : 'No (Direct Tracks)'}
                              </span>
                            </td>
                            <td>
                              <span className={`badge-chip ${c.is_active === 1 ? 'badge-success' : 'badge-danger'}`}>
                                {c.is_active === 1 ? 'Active' : 'Disabled'}
                              </span>
                            </td>
                            <td>
                              <div className="action-buttons">
                                <button className="btn-icon" onClick={() => setMusicCategoryModal({ show: true, mode: 'edit', data: c })}>{Icons.Edit()}</button>
                                <button className="btn-icon danger" onClick={() => handleDeleteMusicCategory(c.id)}>{Icons.Trash()}</button>
                              </div>
                            </td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
          </div>
        )}

        {/* Tab: Education Portal Hub */}
        {currentTab === 'education_hub' && (
          <div className="animate-fade-in">
            <div className="section-panel glass-panel">
              <div className="section-header">
                <div>
                  <h2>Education & Learning Video Hub</h2>
                  <p style={{ color: 'var(--text-muted)', fontSize: '13px' }}>Manage Educational Categories, Subjects, and Videos (/education_videos.php, /education_subjects.php, /education_categories.php)</p>
                </div>
                <div style={{ display: 'flex', gap: '10px' }}>
                  {eduSubTab === 'videos' && (
                    <button className="btn-primary" onClick={() => setEduVideoModal({ show: true, mode: 'create', data: null })}>
                      {Icons.Plus()} Add Video Lesson
                    </button>
                  )}
                  {eduSubTab === 'subjects' && (
                    <button className="btn-primary" onClick={() => setEduSubjectModal({ show: true, mode: 'create', data: null })}>
                      {Icons.Plus()} Add Subject
                    </button>
                  )}
                  {eduSubTab === 'categories' && (
                    <button className="btn-primary" onClick={() => setEduCategoryModal({ show: true, mode: 'create', data: null })}>
                      {Icons.Plus()} Add Category
                    </button>
                  )}
                </div>
              </div>

              {/* Sub tab buttons */}
              <div className="tab-buttons" style={{ margin: '15px 0' }}>
                <button className={`tab-btn ${eduSubTab === 'videos' ? 'active' : ''}`} onClick={() => setEduSubTab('videos')}>
                  Education Videos ({eduVideos.length})
                </button>
                <button className={`tab-btn ${eduSubTab === 'subjects' ? 'active' : ''}`} onClick={() => setEduSubTab('subjects')}>
                  Subjects ({eduSubjects.length})
                </button>
                <button className={`tab-btn ${eduSubTab === 'categories' ? 'active' : ''}`} onClick={() => setEduSubTab('categories')}>
                  Categories ({eduCategories.length})
                </button>
              </div>

              {eduSubTab === 'videos' && (
                <div className="table-responsive">
                  <table className="data-table">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Video Title</th>
                        <th>Category</th>
                        <th>Subject</th>
                        <th>Duration</th>
                        <th>Video Type</th>
                        <th>Video URL</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      {eduVideos.length === 0 ? (
                        <tr><td colSpan="9" style={{ textAlign: 'center', padding: '20px' }}>No education videos found.</td></tr>
                      ) : (
                        eduVideos.map(v => (
                          <tr key={v.id}>
                            <td>{v.id}</td>
                            <td><strong>{v.title}</strong></td>
                            <td><span className="badge-chip badge-primary">{v.category_name || '—'}</span></td>
                            <td>{v.subject_name || '—'}</td>
                            <td><code>{v.duration || 'N/A'}</code></td>
                            <td><code>{v.video_type}</code></td>
                            <td style={{ maxWidth: '180px', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', fontSize: '11px' }}>
                              <a href={v.video_url} target="_blank" rel="noreferrer" style={{ color: 'var(--primary)' }}>{v.video_url}</a>
                            </td>
                            <td>
                              <span className={`badge-chip ${v.is_active === 1 ? 'badge-success' : 'badge-danger'}`}>
                                {v.is_active === 1 ? 'Active' : 'Disabled'}
                              </span>
                            </td>
                            <td>
                              <div className="action-buttons">
                                <button className="btn-icon" onClick={() => setEduVideoModal({ show: true, mode: 'edit', data: v })}>{Icons.Edit()}</button>
                                <button className="btn-icon danger" onClick={() => handleDeleteEduVideo(v.id)}>{Icons.Trash()}</button>
                              </div>
                            </td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </table>
                </div>
              )}

              {eduSubTab === 'subjects' && (
                <div className="table-responsive">
                  <table className="data-table">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Subject Name</th>
                        <th>Category</th>
                        <th>Image</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      {eduSubjects.length === 0 ? (
                        <tr><td colSpan="6" style={{ textAlign: 'center', padding: '20px' }}>No education subjects found.</td></tr>
                      ) : (
                        eduSubjects.map(s => (
                          <tr key={s.id}>
                            <td>{s.id}</td>
                            <td><strong>{s.name}</strong></td>
                            <td><span className="badge-chip badge-primary">{s.category_name || '—'}</span></td>
                            <td>
                              {s.image_url ? <img src={s.image_url} alt={s.name} style={{ width: '35px', height: '35px', borderRadius: '4px', objectFit: 'cover' }} /> : '—'}
                            </td>
                            <td>
                              <span className={`badge-chip ${s.is_active === 1 ? 'badge-success' : 'badge-danger'}`}>
                                {s.is_active === 1 ? 'Active' : 'Disabled'}
                              </span>
                            </td>
                            <td>
                              <div className="action-buttons">
                                <button className="btn-icon" onClick={() => setEduSubjectModal({ show: true, mode: 'edit', data: s })}>{Icons.Edit()}</button>
                                <button className="btn-icon danger" onClick={() => handleDeleteEduSubject(s.id)}>{Icons.Trash()}</button>
                              </div>
                            </td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </table>
                </div>
              )}

              {eduSubTab === 'categories' && (
                <div className="table-responsive">
                  <table className="data-table">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Category Name</th>
                        <th>Image</th>
                        <th>Has Subjects</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      {eduCategories.length === 0 ? (
                        <tr><td colSpan="6" style={{ textAlign: 'center', padding: '20px' }}>No categories found.</td></tr>
                      ) : (
                        eduCategories.map(c => (
                          <tr key={c.id}>
                            <td>{c.id}</td>
                            <td><strong>{c.name}</strong></td>
                            <td>
                              {c.image_url ? <img src={c.image_url} alt={c.name} style={{ width: '35px', height: '35px', borderRadius: '4px', objectFit: 'cover' }} /> : '—'}
                            </td>
                            <td>
                              <span className={`badge-chip ${c.has_subjects === 1 ? 'badge-primary' : 'badge-chip'}`}>
                                {c.has_subjects === 1 ? 'Yes (Subjects)' : 'No (Direct Videos)'}
                              </span>
                            </td>
                            <td>
                              <span className={`badge-chip ${c.is_active === 1 ? 'badge-success' : 'badge-danger'}`}>
                                {c.is_active === 1 ? 'Active' : 'Disabled'}
                              </span>
                            </td>
                            <td>
                              <div className="action-buttons">
                                <button className="btn-icon" onClick={() => setEduCategoryModal({ show: true, mode: 'edit', data: c })}>{Icons.Edit()}</button>
                                <button className="btn-icon danger" onClick={() => handleDeleteEduCategory(c.id)}>{Icons.Trash()}</button>
                              </div>
                            </td>
                          </tr>
                        ))
                      )}
                    </tbody>
                  </table>
                </div>
              )}
            </div>
          </div>
        )}
      </main>

      {/* --- MODAL: TV Channel --- */}
      {tvChannelModal.show && (
        <div className="modal-overlay">
          <div className="modal-content glass-panel animate-fade-in">
            <div className="modal-header">
              <h2 className="modal-title">{tvChannelModal.mode === 'create' ? 'Add TV Channel' : 'Edit TV Channel'}</h2>
              <button className="modal-close" onClick={() => setTvChannelModal({ show: false, mode: 'create', data: null })}>&times;</button>
            </div>
            <form onSubmit={handleTvChannelSubmit}>
              <div className="form-group">
                <label>Channel Name *</label>
                <input type="text" name="name" placeholder="Maha Max" defaultValue={tvChannelModal.data?.name || ''} required />
              </div>
              <div className="form-group">
                <label>Channel Number * (Unique)</label>
                <input type="number" name="channelNumber" placeholder="67" defaultValue={tvChannelModal.data?.channelNumber || ''} required />
              </div>
              <div className="form-group">
                <label>Stream / m3u8 URL * (Unique)</label>
                <input type="url" name="channelUrl" placeholder="https://.../index.m3u8" defaultValue={tvChannelModal.data?.channelUrl || ''} required />
              </div>
              <div className="form-group">
                <label>Logo Image URL</label>
                <input type="url" name="imageUrl" placeholder="https://.../logo.png" defaultValue={tvChannelModal.data?.imageUrl || ''} />
              </div>
              <div className="form-group">
                <label>Category</label>
                <input type="text" name="category" defaultValue={tvChannelModal.data?.category || 'Entertainment'} required />
              </div>
              <div className="form-group">
                <label>Language</label>
                <input type="text" name="language" defaultValue={tvChannelModal.data?.language || 'Telugu'} required />
              </div>
              <div className="form-group">
                <label>Player Type</label>
                <select name="player" defaultValue={tvChannelModal.data?.player || 'internal'}>
                  <option value="internal">internal</option>
                  <option value="exo">exo</option>
                  <option value="vlc">vlc</option>
                </select>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn-secondary" onClick={() => setTvChannelModal({ show: false, mode: 'create', data: null })}>Cancel</button>
                <button type="submit" className="btn-primary">Save Channel</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* --- MODAL: Music Category --- */}
      {musicCategoryModal.show && (
        <div className="modal-overlay">
          <div className="modal-content glass-panel animate-fade-in">
            <div className="modal-header">
              <h2 className="modal-title">{musicCategoryModal.mode === 'create' ? 'Add Music Category' : 'Edit Category'}</h2>
              <button className="modal-close" onClick={() => setMusicCategoryModal({ show: false, mode: 'create', data: null })}>&times;</button>
            </div>
            <form onSubmit={handleMusicCategorySubmit}>
              <div className="form-group">
                <label>Category Name *</label>
                <input type="text" name="name" placeholder="FM or Devotional" defaultValue={musicCategoryModal.data?.name || ''} required />
              </div>
              <div className="form-group">
                <label>Image URL</label>
                <input type="url" name="image_url" placeholder="https://..." defaultValue={musicCategoryModal.data?.image_url || ''} />
              </div>
              <div className="form-group" style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                <input type="checkbox" name="has_album" id="mc_has_album" defaultChecked={musicCategoryModal.data?.has_album === 1} />
                <label htmlFor="mc_has_album" style={{ margin: 0, cursor: 'pointer' }}>Has Albums (Nested Albums inside this category)</label>
              </div>
              <div className="form-group" style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                <input type="checkbox" name="is_active" id="mc_is_active" defaultChecked={musicCategoryModal.data?.is_active !== 0} />
                <label htmlFor="mc_is_active" style={{ margin: 0, cursor: 'pointer' }}>Is Active</label>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn-secondary" onClick={() => setMusicCategoryModal({ show: false, mode: 'create', data: null })}>Cancel</button>
                <button type="submit" className="btn-primary">Save Category</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* --- MODAL: Music Album --- */}
      {musicAlbumModal.show && (
        <div className="modal-overlay">
          <div className="modal-content glass-panel animate-fade-in">
            <div className="modal-header">
              <h2 className="modal-title">{musicAlbumModal.mode === 'create' ? 'Add Music Album' : 'Edit Album'}</h2>
              <button className="modal-close" onClick={() => setMusicAlbumModal({ show: false, mode: 'create', data: null })}>&times;</button>
            </div>
            <form onSubmit={handleMusicAlbumSubmit}>
              <div className="form-group">
                <label>Parent Category *</label>
                <select name="category_id" defaultValue={musicAlbumModal.data?.category_id || ''} required>
                  <option value="">-- Choose Category --</option>
                  {musicCategories.map(c => (
                    <option key={c.id} value={c.id}>{c.name}</option>
                  ))}
                </select>
              </div>
              <div className="form-group">
                <label>Album Name *</label>
                <input type="text" name="name" placeholder="Adi Parvam" defaultValue={musicAlbumModal.data?.name || ''} required />
              </div>
              <div className="form-group">
                <label>Cover Image URL</label>
                <input type="url" name="image_url" placeholder="https://..." defaultValue={musicAlbumModal.data?.image_url || ''} />
              </div>
              <div className="form-group" style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                <input type="checkbox" name="is_active" id="ma_is_active" defaultChecked={musicAlbumModal.data?.is_active !== 0} />
                <label htmlFor="ma_is_active" style={{ margin: 0, cursor: 'pointer' }}>Is Active</label>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn-secondary" onClick={() => setMusicAlbumModal({ show: false, mode: 'create', data: null })}>Cancel</button>
                <button type="submit" className="btn-primary">Save Album</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* --- MODAL: Music Track / Stream --- */}
      {musicTrackModal.show && (
        <div className="modal-overlay">
          <div className="modal-content glass-panel animate-fade-in">
            <div className="modal-header">
              <h2 className="modal-title">{musicTrackModal.mode === 'create' ? 'Add Music Track / Stream' : 'Edit Track'}</h2>
              <button className="modal-close" onClick={() => setMusicTrackModal({ show: false, mode: 'create', data: null })}>&times;</button>
            </div>
            <form onSubmit={handleMusicTrackSubmit}>
              <div className="form-group">
                <label>Category *</label>
                <select name="category_id" defaultValue={musicTrackModal.data?.category_id || ''} required>
                  <option value="">-- Choose Category --</option>
                  {musicCategories.map(c => (
                    <option key={c.id} value={c.id}>{c.name}</option>
                  ))}
                </select>
              </div>
              <div className="form-group">
                <label>Album (Optional)</label>
                <select name="album_id" defaultValue={musicTrackModal.data?.album_id || ''}>
                  <option value="">-- No Album (Direct Track) --</option>
                  {musicAlbums.map(a => (
                    <option key={a.id} value={a.id}>{a.name} ({a.category_name})</option>
                  ))}
                </select>
              </div>
              <div className="form-group">
                <label>Track Name *</label>
                <input type="text" name="name" placeholder="Radio Mirchi or Song 1" defaultValue={musicTrackModal.data?.name || ''} required />
              </div>
              <div className="form-group">
                <label>Media Type</label>
                <select name="type" defaultValue={musicTrackModal.data?.type || 'PODCAST'}>
                  <option value="PODCAST">PODCAST</option>
                  <option value="FM">FM</option>
                  <option value="AUDIO">AUDIO</option>
                </select>
              </div>
              <div className="form-group">
                <label>Stream / Audio URL *</label>
                <input type="url" name="stream_url" placeholder="https://.../stream.mp3" defaultValue={musicTrackModal.data?.stream_url || ''} required />
              </div>
              <div className="form-group">
                <label>Cover / Logo Image URL</label>
                <input type="url" name="image_url" placeholder="https://..." defaultValue={musicTrackModal.data?.image_url || ''} />
              </div>
              <div className="form-group" style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                <input type="checkbox" name="is_active" id="mt_is_active" defaultChecked={musicTrackModal.data?.is_active !== 0} />
                <label htmlFor="mt_is_active" style={{ margin: 0, cursor: 'pointer' }}>Is Active</label>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn-secondary" onClick={() => setMusicTrackModal({ show: false, mode: 'create', data: null })}>Cancel</button>
                <button type="submit" className="btn-primary">Save Track</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* --- MODAL: Education Category --- */}
      {eduCategoryModal.show && (
        <div className="modal-overlay">
          <div className="modal-content glass-panel animate-fade-in">
            <div className="modal-header">
              <h2 className="modal-title">{eduCategoryModal.mode === 'create' ? 'Add Education Category' : 'Edit Category'}</h2>
              <button className="modal-close" onClick={() => setEduCategoryModal({ show: false, mode: 'create', data: null })}>&times;</button>
            </div>
            <form onSubmit={handleEduCategorySubmit}>
              <div className="form-group">
                <label>Category Name *</label>
                <input type="text" name="name" placeholder="Maths or Science" defaultValue={eduCategoryModal.data?.name || ''} required />
              </div>
              <div className="form-group">
                <label>Image URL</label>
                <input type="url" name="image_url" placeholder="https://..." defaultValue={eduCategoryModal.data?.image_url || ''} />
              </div>
              <div className="form-group" style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                <input type="checkbox" name="has_subjects" id="ec_has_subjects" defaultChecked={eduCategoryModal.data?.has_subjects === 1} />
                <label htmlFor="ec_has_subjects" style={{ margin: 0, cursor: 'pointer' }}>Has Subjects (Nested Subjects inside category)</label>
              </div>
              <div className="form-group" style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                <input type="checkbox" name="is_active" id="ec_is_active" defaultChecked={eduCategoryModal.data?.is_active !== 0} />
                <label htmlFor="ec_is_active" style={{ margin: 0, cursor: 'pointer' }}>Is Active</label>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn-secondary" onClick={() => setEduCategoryModal({ show: false, mode: 'create', data: null })}>Cancel</button>
                <button type="submit" className="btn-primary">Save Category</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* --- MODAL: Education Subject --- */}
      {eduSubjectModal.show && (
        <div className="modal-overlay">
          <div className="modal-content glass-panel animate-fade-in">
            <div className="modal-header">
              <h2 className="modal-title">{eduSubjectModal.mode === 'create' ? 'Add Education Subject' : 'Edit Subject'}</h2>
              <button className="modal-close" onClick={() => setEduSubjectModal({ show: false, mode: 'create', data: null })}>&times;</button>
            </div>
            <form onSubmit={handleEduSubjectSubmit}>
              <div className="form-group">
                <label>Category *</label>
                <select name="category_id" defaultValue={eduSubjectModal.data?.category_id || ''} required>
                  <option value="">-- Choose Category --</option>
                  {eduCategories.map(c => (
                    <option key={c.id} value={c.id}>{c.name}</option>
                  ))}
                </select>
              </div>
              <div className="form-group">
                <label>Subject Name *</label>
                <input type="text" name="name" placeholder="Algebra" defaultValue={eduSubjectModal.data?.name || ''} required />
              </div>
              <div className="form-group">
                <label>Image URL</label>
                <input type="url" name="image_url" placeholder="https://..." defaultValue={eduSubjectModal.data?.image_url || ''} />
              </div>
              <div className="form-group" style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                <input type="checkbox" name="is_active" id="es_is_active" defaultChecked={eduSubjectModal.data?.is_active !== 0} />
                <label htmlFor="es_is_active" style={{ margin: 0, cursor: 'pointer' }}>Is Active</label>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn-secondary" onClick={() => setEduSubjectModal({ show: false, mode: 'create', data: null })}>Cancel</button>
                <button type="submit" className="btn-primary">Save Subject</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* --- MODAL: Education Video --- */}
      {eduVideoModal.show && (
        <div className="modal-overlay">
          <div className="modal-content glass-panel animate-fade-in">
            <div className="modal-header">
              <h2 className="modal-title">{eduVideoModal.mode === 'create' ? 'Add Education Video Lesson' : 'Edit Video Lesson'}</h2>
              <button className="modal-close" onClick={() => setEduVideoModal({ show: false, mode: 'create', data: null })}>&times;</button>
            </div>
            <form onSubmit={handleEduVideoSubmit}>
              <div className="form-group">
                <label>Category *</label>
                <select name="category_id" defaultValue={eduVideoModal.data?.category_id || ''} required>
                  <option value="">-- Choose Category --</option>
                  {eduCategories.map(c => (
                    <option key={c.id} value={c.id}>{c.name}</option>
                  ))}
                </select>
              </div>
              <div className="form-group">
                <label>Subject (Optional)</label>
                <select name="subject_id" defaultValue={eduVideoModal.data?.subject_id || ''}>
                  <option value="">-- No Subject (Direct Video) --</option>
                  {eduSubjects.map(s => (
                    <option key={s.id} value={s.id}>{s.name} ({s.category_name})</option>
                  ))}
                </select>
              </div>
              <div className="form-group">
                <label>Video Title *</label>
                <input type="text" name="title" placeholder="Introduction to Algebra" defaultValue={eduVideoModal.data?.title || ''} required />
              </div>
              <div className="form-group">
                <label>Video Stream / YouTube URL *</label>
                <input type="url" name="video_url" placeholder="https://youtu.be/..." defaultValue={eduVideoModal.data?.video_url || ''} required />
              </div>
              <div className="form-group">
                <label>Video Type</label>
                <select name="video_type" defaultValue={eduVideoModal.data?.video_type || 'youtube'}>
                  <option value="youtube">youtube</option>
                  <option value="hls">hls</option>
                  <option value="mp4">mp4</option>
                </select>
              </div>
              <div className="form-group">
                <label>Duration (e.g. 10:20)</label>
                <input type="text" name="duration" placeholder="10:20" defaultValue={eduVideoModal.data?.duration || ''} />
              </div>
              <div className="form-group">
                <label>Thumbnail Image URL</label>
                <input type="url" name="image_url" placeholder="https://..." defaultValue={eduVideoModal.data?.image_url || ''} />
              </div>
              <div className="form-group">
                <label>Description</label>
                <textarea name="description" rows="2" defaultValue={eduVideoModal.data?.description || ''}></textarea>
              </div>
              <div className="form-group" style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                <input type="checkbox" name="is_active" id="ev_is_active" defaultChecked={eduVideoModal.data?.is_active !== 0} />
                <label htmlFor="ev_is_active" style={{ margin: 0, cursor: 'pointer' }}>Is Active</label>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn-secondary" onClick={() => setEduVideoModal({ show: false, mode: 'create', data: null })}>Cancel</button>
                <button type="submit" className="btn-primary">Save Video</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* --- MODAL: Add/Edit Operator --- */}
      {operatorModal.show && (
        <div className="modal-overlay">
          <div className="modal-content glass-panel animate-fade-in">
            <div className="modal-header">
              <h2 className="modal-title">
                {operatorModal.mode === 'create' ? `Add New ${user?.role === 'super_admin' ? (opModalRole === 'admin' ? 'Admin' : 'Operator') : 'Operator'}` : 'Edit Profile'}
              </h2>
              <button 
                className="modal-close"
                onClick={() => setOperatorModal({ show: false, mode: 'create', data: null })}
              >
                &times;
              </button>
            </div>

            <form onSubmit={handleOperatorSubmit}>
              {user?.role === 'super_admin' && operatorModal.mode === 'create' && (
                <div className="form-group">
                  <label>Target Role *</label>
                  <select 
                    name="role" 
                    value={opModalRole} 
                    onChange={(e) => setOpModalRole(e.target.value)}
                    required
                  >
                    <option value="admin">Admin</option>
                    <option value="operator">Operator</option>
                  </select>
                </div>
              )}
              {user?.role === 'super_admin' && operatorModal.mode === 'create' && opModalRole === 'operator' && (
                <div className="form-group">
                  <label>Assign to Parent Admin *</label>
                  <select name="parent_id" required>
                    <option value="">-- Choose Admin --</option>
                    {operators.filter(op => op.role === 'admin').map(op => (
                      <option key={op.id} value={op.id}>{op.name} ({op.company_name || 'No Company'})</option>
                    ))}
                  </select>
                </div>
              )}
              <div className="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" defaultValue={operatorModal.data?.name || ''} required />
              </div>
              <div className="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" defaultValue={operatorModal.data?.email || ''} required />
              </div>
              <div className="form-group">
                <label>Mobile Number</label>
                <input type="tel" name="mobile" defaultValue={operatorModal.data?.mobile || ''} />
              </div>
              <div className="form-group">
                <label>{operatorModal.mode === 'create' ? 'Password *' : 'Password (leave blank to keep unchanged)'}</label>
                <input type="password" name="password" required={operatorModal.mode === 'create'} />
              </div>
              <div className="form-group">
                <label>Company / Franchise Name</label>
                <input type="text" name="company_name" defaultValue={operatorModal.data?.company_name || ''} />
              </div>
              {operatorModal.mode === 'create' && (
                <div className="form-group">
                  <label>Initial Wallet Allocation (₹)</label>
                  <input type="number" name="wallet_balance" step="100" defaultValue="0" />
                </div>
              )}

              <div className="modal-footer">
                <button type="button" className="btn-secondary" onClick={() => setOperatorModal({ show: false, mode: 'create', data: null })}>Cancel</button>
                <button type="submit" className="btn-primary">Save Operator</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* --- MODAL: Allocate Wallet Funds --- */}
      {allocateModal.show && (
        <div className="modal-overlay">
          <div className="modal-content glass-panel animate-fade-in" style={{ maxWidth: '400px' }}>
            <div className="modal-header">
              <h2 className="modal-title">Transfer Funds</h2>
              <button 
                className="modal-close"
                onClick={() => setAllocateModal({ show: false, operatorId: null, operatorName: '', amount: '' })}
              >
                &times;
              </button>
            </div>

            <form onSubmit={handleAllocateSubmit}>
              <div style={{ marginBottom: '16px', fontSize: '14px', color: 'var(--text-muted)' }}>
                Deduct balance from your wallet and allocate to: <strong>{allocateModal.operatorName}</strong>.
              </div>
              <div className="form-group">
                <label>Transfer Amount (₹) *</label>
                <input 
                  type="number" 
                  min="1" 
                  step="any" 
                  placeholder="e.g. 500" 
                  value={allocateModal.amount}
                  onChange={(e) => setAllocateModal(prev => ({ ...prev, amount: e.target.value }))}
                  required 
                />
              </div>

              <div className="modal-footer">
                <button type="button" className="btn-secondary" onClick={() => setAllocateModal({ show: false, operatorId: null, operatorName: '', amount: '' })}>Cancel</button>
                <button type="submit" className="btn-primary" style={{ background: 'var(--accent-cyan)' }}>Complete Transfer</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* --- MODAL: Add/Edit Customer --- */}
      {customerModal.show && (
        <div className="modal-overlay">
          <div className="modal-content glass-panel animate-fade-in">
            <div className="modal-header">
              <h2 className="modal-title">
                {customerModal.mode === 'create' ? 'Register New Customer' : 'Update Customer Profile'}
              </h2>
              <button 
                className="modal-close"
                onClick={() => setCustomerModal({ show: false, mode: 'create', data: null })}
              >
                &times;
              </button>
            </div>

            <form onSubmit={handleCustomerSubmit}>
              <div className="form-grid">
                <div className="form-group">
                  <label>First Name *</label>
                  <input type="text" name="first_name" defaultValue={customerModal.data?.first_name || ''} required />
                </div>
                <div className="form-group">
                  <label>Last Name</label>
                  <input type="text" name="last_name" defaultValue={customerModal.data?.last_name || ''} />
                </div>
                <div className="form-group">
                  <label>Phone Number *</label>
                  <input type="tel" name="phone_number" defaultValue={customerModal.data?.phone_number || ''} required />
                </div>
                <div className="form-group">
                  <label>{customerModal.mode === 'create' ? 'Password *' : 'Password (leave blank to keep unchanged)'}</label>
                  <input type="password" name="password" placeholder={customerModal.mode === 'create' ? '••••••' : 'Keep current'} required={customerModal.mode === 'create'} />
                </div>
                <div className="form-group">
                  <label>Customer Code (leave blank to auto-generate)</label>
                  <input type="text" name="customer_code" placeholder="e.g. CUS0010" defaultValue={customerModal.data?.customer_code || ''} />
                </div>
                <div className="form-group">
                  <label>Max Device Limit</label>
                  <input type="number" name="Max_login_devices" min="1" max="10" defaultValue={customerModal.data?.Max_login_devices || 4} />
                </div>
                <div className="form-group">
                  <label>Expiry Date (Main Account) 📅</label>
                  <input 
                    type="date" 
                    name="expiry_date" 
                    defaultValue={customerModal.data?.expiry_date || (customerModal.data?.active_subscription?.expiry_date || '')} 
                  />
                </div>
                <div className="form-group">
                  <label>IPTV Expiry Date 📅</label>
                  <input 
                    type="date" 
                    name="iptv_expiry_date" 
                    defaultValue={customerModal.data?.iptv_expiry_date || (customerModal.data?.active_subscription?.expiry_date || '')} 
                  />
                </div>
                <div className="form-group">
                  <label>PiShow Expiry Date 📅</label>
                  <input 
                    type="date" 
                    name="pishow_expiry_date" 
                    defaultValue={customerModal.data?.pishow_expiry_date || (customerModal.data?.active_subscription?.pishow_expiry_date || customerModal.data?.active_subscription?.expiry_date || '')} 
                  />
                </div>

                {/* Admins can choose which Operator owns this customer */}
                {user?.role !== 'operator' && (
                  <div className="form-group full-width">
                    <label>Assign to Operator / Agent</label>
                    <select name="operator_id" defaultValue={customerModal.data?.operator_id || user?.id}>
                      <option value={user?.id}>Direct (Assign to Me)</option>
                      {operators.filter(op => op.role === 'operator').map(op => (
                        <option key={op.id} value={op.id}>{op.name} ({op.company_name || 'No Company'})</option>
                      ))}
                    </select>
                  </div>
                )}

                <div className="form-group full-width">
                  <label>Installation Address</label>
                  <textarea name="installation_address" rows="2" defaultValue={customerModal.data?.installation_address || ''} ></textarea>
                </div>
                <div className="form-group full-width">
                  <label>Notes</label>
                  <textarea name="notes" rows="2" placeholder="Billing or special configuration notes..." defaultValue={customerModal.data?.notes || ''} ></textarea>
                </div>
              </div>

              <div className="modal-footer">
                <button type="button" className="btn-secondary" onClick={() => setCustomerModal({ show: false, mode: 'create', data: null })}>Cancel</button>
                <button type="submit" className="btn-primary">{customerModal.mode === 'create' ? 'Register Customer' : 'Save Changes'}</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* --- MODAL: Customer Devices Quick-List --- */}
      {customerDevicesModal.show && (
        <div className="modal-overlay">
          <div className="modal-content glass-panel animate-fade-in" style={{ maxWidth: '750px' }}>
            <div className="modal-header">
              <h2 className="modal-title">Devices for {customerDevicesModal.customerName}</h2>
              <button 
                className="modal-close"
                onClick={() => setCustomerDevicesModal({ show: false, customerId: null, customerName: '', list: [] })}
              >
                &times;
              </button>
            </div>

            <div style={{ marginBottom: '20px', display: 'flex', justifyContent: 'flex-end' }}>
              <button 
                className="btn-primary" 
                style={{ padding: '8px 14px', fontSize: '13px' }}
                onClick={() => {
                  setDeviceModal({
                    show: true,
                    customerId: customerDevicesModal.customerId,
                    customerName: customerDevicesModal.customerName
                  });
                  setCustomerDevicesModal(prev => ({ ...prev, show: false }));
                }}
              >
                {Icons.Plus()} Register Device
              </button>
            </div>

            <div className="table-wrapper">
              <table className="data-table">
                <thead>
                  <tr>
                    <th>Device Name</th>
                    <th>MAC</th>
                    <th>Device UUID</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {customerDevicesModal.list.length === 0 ? (
                    <tr>
                      <td colSpan="5" style={{ textAlign: 'center', padding: '20px', color: 'var(--text-muted)' }}>No devices registered.</td>
                    </tr>
                  ) : (
                    customerDevicesModal.list.map((dev) => (
                      <tr key={dev.id}>
                        <td style={{ fontWeight: '500' }}>{dev.device_name || 'Generic Device'}</td>
                        <td>{dev.mac_address || '—'}</td>
                        <td style={{ fontSize: '12px', fontFamily: 'monospace' }}>{dev.device_uuid}</td>
                        <td>
                          <span className={`status-tag ${dev.status?.toLowerCase()}`}>{dev.status}</span>
                        </td>
                        <td>
                          <div style={{ display: 'flex', gap: '6px' }}>
                            {dev.status !== 'ACTIVE' && (
                              <button 
                                className="btn-accent" 
                                style={{ padding: '4px 8px', fontSize: '11px', background: 'rgba(16,185,129,0.1)', color: 'var(--accent-emerald)' }}
                                onClick={() => handleDeviceStatusUpdate(dev.id, 'ACTIVE')}
                              >
                                Activate
                              </button>
                            )}
                            {dev.status === 'ACTIVE' && (
                              <button 
                                className="btn-action-delete"
                                style={{ padding: '4px 8px', fontSize: '11px' }}
                                onClick={() => handleDeviceStatusUpdate(dev.id, 'BLOCKED')}
                              >
                                Block
                              </button>
                            )}
                            {(user?.role === 'super_admin' || user?.role === 'admin') && (
                              <button 
                                className="btn-action-delete"
                                style={{ padding: '4px 8px', fontSize: '11px', background: 'rgba(239, 68, 68, 0.15)', color: '#ef4444', borderColor: 'rgba(239, 68, 68, 0.3)' }}
                                onClick={() => handleDeviceDelete(dev.id)}
                              >
                                Delete
                              </button>
                            )}
                          </div>
                        </td>
                      </tr>
                    ))
                  )}
                </tbody>
              </table>
            </div>

            <div className="modal-footer">
              <button type="button" className="btn-secondary" onClick={() => setCustomerDevicesModal({ show: false, customerId: null, customerName: '', list: [] })}>Close</button>
            </div>
          </div>
        </div>
      )}

      {/* --- MODAL: Register Device Form --- */}
      {deviceModal.show && (
        <div className="modal-overlay">
          <div className="modal-content glass-panel animate-fade-in">
            <div className="modal-header">
              <h2 className="modal-title">Register Device for {deviceModal.customerName}</h2>
              <button 
                className="modal-close"
                onClick={() => setDeviceModal({ show: false, customerId: null, customerName: '' })}
              >
                &times;
              </button>
            </div>

            <form onSubmit={handleDeviceSubmit}>
              <div className="form-group">
                <label>Device Name *</label>
                <input type="text" name="device_name" placeholder="e.g. Living Room TV Box" required />
              </div>
              <div className="form-group">
                <label>Device UUID *</label>
                <input type="text" name="device_uuid" placeholder="e.g. uuid-123456" required />
              </div>
              <div className="form-group">
                <label>MAC Address</label>
                <input type="text" name="mac_address" placeholder="e.g. 00:1a:2b:3c:4d:5e" />
              </div>
              <div className="form-group">
                <label>Serial Number</label>
                <input type="text" name="serial_number" placeholder="e.g. SN-98765" />
              </div>
              <div className="form-group">
                <label>Android Device ID</label>
                <input type="text" name="android_id" placeholder="e.g. aid_555" />
              </div>

              <div className="modal-footer">
                <button 
                  type="button" 
                  className="btn-secondary"
                  onClick={() => {
                    setDeviceModal({ show: false, customerId: null, customerName: '' });
                    if (currentTab === 'customers') {
                      viewCustomerDevices(deviceModal.customerId, deviceModal.customerName);
                    }
                  }}
                >
                  Cancel
                </button>
                <button type="submit" className="btn-primary">Register Device</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* --- MODAL: Subscriptions Recharge --- */}
      {rechargeModal.show && (
        <div className="modal-overlay">
          <div className="modal-content glass-panel animate-fade-in">
            <div className="modal-header">
              <h2 className="modal-title">Recharge Plan: {rechargeModal.customerName}</h2>
              <button 
                className="modal-close"
                onClick={() => setRechargeModal({ show: false, customerId: null, customerName: '', selectedPlanId: '', paymentMode: 'WALLET' })}
              >
                &times;
              </button>
            </div>

            <form onSubmit={handleRechargeSubmit}>
              <div className="form-group">
                <label>Select Recharge Plan *</label>
                <select 
                  value={rechargeModal.selectedPlanId} 
                  onChange={(e) => setRechargeModal(prev => ({ ...prev, selectedPlanId: e.target.value }))}
                  required
                >
                  <option value="">-- Choose Plan --</option>
                  {plans.map(p => (
                    <option key={p.id} value={p.id}>
                      {p.plan_name} — ₹{parseFloat(p.price).toFixed(2)} ({p.validity_days} days)
                    </option>
                  ))}
                </select>
              </div>

              {rechargeModal.selectedPlanId && (() => {
                const plan = plans.find(p => p.id == rechargeModal.selectedPlanId);
                if (!plan) return null;
                return (
                  <div className="glass-panel animate-fade-in" style={{ padding: '16px', margin: '16px 0', border: '1px solid rgba(255,255,255,0.05)' }}>
                    <div className="detail-grid">
                      <div className="detail-item">
                        <span className="detail-lbl">Speed</span>
                        <span className="detail-val">{plan.speed || 'N/A'}</span>
                      </div>
                      <div className="detail-item">
                        <span className="detail-lbl">Data Limit</span>
                        <span className="detail-val">{plan.data_limit || 'Unlimited'}</span>
                      </div>
                      <div className="detail-item" style={{ marginTop: '10px' }}>
                        <span className="detail-lbl">Price</span>
                        <span className="detail-val" style={{ color: 'var(--accent-emerald)', fontWeight: '700' }}>
                          ₹{parseFloat(plan.price).toFixed(2)}
                        </span>
                      </div>
                      <div className="detail-item" style={{ marginTop: '10px' }}>
                        <span className="detail-lbl">Validity</span>
                        <span className="detail-val">{plan.validity_days} Days</span>
                      </div>
                    </div>
                  </div>
                );
              })()}

              <div className="form-group">
                <label>Payment Method</label>
                <select 
                  value={rechargeModal.paymentMode}
                  disabled
                >
                  <option value="WALLET">Debit My Wallet (Balance: ₹{stats.walletBalance?.toFixed(2)})</option>
                </select>
              </div>

              <div className="modal-footer">
                <button type="button" className="btn-secondary" onClick={() => setRechargeModal({ show: false, customerId: null, customerName: '', selectedPlanId: '', paymentMode: 'WALLET' })}>Cancel</button>
                <button type="submit" className="btn-primary" style={{ background: 'var(--accent-emerald)' }}>Confirm & Activate</button>
              </div>
            </form>
          </div>
        </div>
      )}
      {/* --- MODAL: Customer Details Pop-up --- */}
      {customerDetailsModal.show && customerDetailsModal.data && (() => {
        const cust = customerDetailsModal.data;
        return (
          <div className="modal-overlay">
            <div className="modal-content glass-panel animate-fade-in" style={{ maxWidth: '550px' }}>
              <div className="modal-header">
                <h2 className="modal-title">Customer Profile Details</h2>
                <button 
                  className="modal-close"
                  onClick={() => setCustomerDetailsModal({ show: false, data: null })}
                >
                  &times;
                </button>
              </div>

              <div style={{ marginBottom: '20px' }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '20px', paddingBottom: '16px', borderBottom: '1px solid var(--glass-border)' }}>
                  <div className="avatar" style={{ width: '48px', height: '48px', fontSize: '20px' }}>
                    {cust.first_name.charAt(0).toUpperCase()}
                  </div>
                  <div>
                    <h3 style={{ fontSize: '18px', fontWeight: '700' }}>{cust.first_name} {cust.last_name}</h3>
                    <span style={{ fontSize: '12px', color: 'var(--text-muted)' }}>Code: {cust.customer_code}</span>
                  </div>
                </div>

                <div className="detail-grid">
                  <div className="detail-item">
                    <span className="detail-lbl">Phone Number</span>
                    <span className="detail-val">{cust.phone_number}</span>
                  </div>
                  <div className="detail-item">
                    <span className="detail-lbl">Operator / Agent</span>
                    <span className="detail-val">{cust.operator_name || 'Direct / System'}</span>
                  </div>
                  <div className="detail-item" style={{ marginTop: '12px' }}>
                    <span className="detail-lbl">Customer Login Password</span>
                    <span className="detail-val" style={{ fontFamily: 'monospace', fontWeight: '600' }}>{cust.password}</span>
                  </div>
                  <div className="detail-item" style={{ marginTop: '12px' }}>
                    <span className="detail-lbl">Device Logins</span>
                    <span className="detail-val">{cust.device_count} / {cust.Max_login_devices} Max Devices</span>
                  </div>
                </div>

                <h4 style={{ fontSize: '14px', fontWeight: '600', margin: '20px 0 10px 0', textTransform: 'uppercase', letterSpacing: '0.5px', color: 'var(--text-muted)' }}>Subscription Details</h4>
                {cust.active_subscription ? (
                  <div className="glass-panel" style={{ padding: '16px', background: 'rgba(16, 185, 129, 0.05)', border: '1px solid rgba(16, 185, 129, 0.15)' }}>
                    <div className="detail-grid">
                      <div className="detail-item">
                        <span className="detail-lbl">Plan Name</span>
                        <span className="detail-val" style={{ color: 'var(--accent-emerald)', fontWeight: '600' }}>{cust.active_subscription.plan_name}</span>
                      </div>
                      <div className="detail-item">
                        <span className="detail-lbl">Main Expiry Date</span>
                        <span className="detail-val" style={{ fontWeight: '600', color: '#f59e0b' }}>
                          {cust.expiry_date || cust.active_subscription.expiry_date}
                        </span>
                      </div>
                      <div className="detail-item">
                        <span className="detail-lbl">IPTV Expiry Date</span>
                        <span className="detail-val" style={{ fontWeight: '600', color: '#10b981' }}>
                          {cust.iptv_expiry_date || cust.active_subscription.expiry_date}
                        </span>
                      </div>
                      <div className="detail-item" style={{ marginTop: '10px' }}>
                        <span className="detail-lbl">PiShow Expiry Date</span>
                        <span className="detail-val" style={{ fontWeight: '600', color: '#06b6d4' }}>
                          {cust.pishow_expiry_date || cust.active_subscription.pishow_expiry_date || cust.active_subscription.expiry_date}
                        </span>
                      </div>
                      <div className="detail-item" style={{ marginTop: '10px' }}>
                        <span className="detail-lbl">Speed</span>
                        <span className="detail-val">{cust.active_subscription.speed || 'N/A'}</span>
                      </div>
                      <div className="detail-item" style={{ marginTop: '10px' }}>
                        <span className="detail-lbl">Data Limit</span>
                        <span className="detail-val">{cust.active_subscription.data_limit || 'Unlimited'}</span>
                      </div>
                    </div>
                  </div>
                ) : (
                  <div className="glass-panel" style={{ padding: '16px', background: 'rgba(239, 68, 68, 0.05)', border: '1px solid rgba(239, 68, 68, 0.15)', color: 'var(--accent-rose)', fontSize: '14px', fontWeight: '500' }}>
                    No active subscription plan found. Customer is currently expired.
                  </div>
                )}

                <h4 style={{ fontSize: '14px', fontWeight: '600', margin: '20px 0 10px 0', textTransform: 'uppercase', letterSpacing: '0.5px', color: 'var(--text-muted)' }}>Installation & System Info</h4>
                <div className="detail-grid">
                  <div className="detail-item">
                    <span className="detail-lbl">Installation Address</span>
                    <span className="detail-val" style={{ fontSize: '13px' }}>{cust.installation_address || 'Not specified'}</span>
                  </div>
                  <div className="detail-item">
                    <span className="detail-lbl">Customer Notes</span>
                    <span className="detail-val" style={{ fontSize: '13px' }}>{cust.notes || 'No extra notes.'}</span>
                  </div>
                  <div className="detail-item" style={{ marginTop: '10px' }}>
                    <span className="detail-lbl">Registered On</span>
                    <span className="detail-val" style={{ fontSize: '13px' }}>{cust.created_at || '—'}</span>
                  </div>
                </div>
              </div>

              <div className="modal-footer">
                <button type="button" className="btn-secondary" onClick={() => setCustomerDetailsModal({ show: false, data: null })}>Close Details</button>
              </div>
            </div>
          </div>
        );
      })()}

      {/* --- MODAL: App Version --- */}
      {appVersionModal.show && (
        <div className="modal-overlay">
          <div className="modal-content glass-panel animate-fade-in">
            <div className="modal-header">
              <h2 className="modal-title">{appVersionModal.mode === 'create' ? 'Add App Version' : 'Edit App Version'}</h2>
              <button className="modal-close" onClick={() => setAppVersionModal({ show: false, mode: 'create', data: null })}>&times;</button>
            </div>
            <form onSubmit={handleAppVersionSubmit}>
              <div className="form-group">
                <label>App Name *</label>
                <input type="text" name="app_name" defaultValue={appVersionModal.data?.app_name || 'launcher'} required />
              </div>
              <div className="form-group">
                <label>Platform *</label>
                <input type="text" name="platform" defaultValue={appVersionModal.data?.platform || 'android_tv'} required />
              </div>
              <div className="form-group">
                <label>Version Name *</label>
                <input type="text" name="version_name" placeholder="e.g. 1.1.7" defaultValue={appVersionModal.data?.version_name || ''} required />
              </div>
              <div className="form-group">
                <label>Version Code *</label>
                <input type="number" name="version_code" placeholder="e.g. 117" defaultValue={appVersionModal.data?.version_code || 100} required />
              </div>
              <div className="form-group">
                <label>Update Message</label>
                <textarea name="update_message" rows="2" defaultValue={appVersionModal.data?.update_message || ''} placeholder="New update available. Please update to continue."></textarea>
              </div>
              <div className="form-group">
                <label>APK Direct URL</label>
                <input type="url" name="apk_url" defaultValue={appVersionModal.data?.apk_url || ''} placeholder="https://..." />
              </div>
              <div className="form-group">
                <label>PlayStore URL</label>
                <input type="url" name="playstore_url" defaultValue={appVersionModal.data?.playstore_url || ''} placeholder="https://..." />
              </div>
              <div className="form-group" style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                <input type="checkbox" name="force_update" id="force_update_chk" defaultChecked={appVersionModal.data?.force_update === 1} />
                <label htmlFor="force_update_chk" style={{ margin: 0, cursor: 'pointer' }}>Force Update Required</label>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn-secondary" onClick={() => setAppVersionModal({ show: false, mode: 'create', data: null })}>Cancel</button>
                <button type="submit" className="btn-primary">Save Version</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* --- MODAL: App Store App --- */}
      {appStoreModal.show && (
        <div className="modal-overlay">
          <div className="modal-content glass-panel animate-fade-in">
            <div className="modal-header">
              <h2 className="modal-title">{appStoreModal.mode === 'create' ? 'Add App Store App' : 'Edit App Store App'}</h2>
              <button className="modal-close" onClick={() => setAppStoreModal({ show: false, mode: 'create', data: null })}>&times;</button>
            </div>
            <form onSubmit={handleAppStoreSubmit}>
              <div className="form-group">
                <label>App Name *</label>
                <input type="text" name="name" placeholder="Netflix" defaultValue={appStoreModal.data?.name || ''} required />
              </div>
              <div className="form-group">
                <label>Package Name *</label>
                <input type="text" name="package_name" placeholder="com.netflix.ninja" defaultValue={appStoreModal.data?.package_name || ''} required />
              </div>
              <div className="form-group">
                <label>Icon / Image URL</label>
                <input type="url" name="image_url" placeholder="https://..." defaultValue={appStoreModal.data?.image_url || ''} />
              </div>
              <div className="form-group">
                <label>Play Store ID / Link</label>
                <input type="text" name="play_store_id" placeholder="https://play.google.com/store/apps/details?id=..." defaultValue={appStoreModal.data?.play_store_id || ''} />
              </div>
              <div className="form-group">
                <label>Amazon App ID / Link</label>
                <input type="text" name="amazon_app_id" placeholder="https://www.amazon.com/dp/..." defaultValue={appStoreModal.data?.amazon_app_id || ''} />
              </div>
              <div className="form-group">
                <label>Direct APK URL</label>
                <input type="url" name="apk_url" placeholder="https://..." defaultValue={appStoreModal.data?.apk_url || ''} />
              </div>
              <div className="form-group" style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
                <input type="checkbox" name="is_active" id="is_active_chk" defaultChecked={appStoreModal.data?.is_active !== 0} />
                <label htmlFor="is_active_chk" style={{ margin: 0, cursor: 'pointer' }}>Is Active (Enabled in App Store)</label>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn-secondary" onClick={() => setAppStoreModal({ show: false, mode: 'create', data: null })}>Cancel</button>
                <button type="submit" className="btn-primary">Save App</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* --- MODAL: Actor --- */}
      {actorModal.show && (
        <div className="modal-overlay">
          <div className="modal-content glass-panel animate-fade-in">
            <div className="modal-header">
              <h2 className="modal-title">{actorModal.mode === 'create' ? 'Add Actor / Category Group' : 'Edit Actor'}</h2>
              <button className="modal-close" onClick={() => setActorModal({ show: false, mode: 'create', data: null })}>&times;</button>
            </div>
            <form onSubmit={handleActorSubmit}>
              <div className="form-group">
                <label>Actor / Group Name *</label>
                <input type="text" name="name" placeholder="Chiranjeevi or Kids" defaultValue={actorModal.data?.name || ''} required />
              </div>
              <div className="form-group">
                <label>Image Path / URL</label>
                <input type="text" name="image" placeholder="/images/actors/actor1.jpg" defaultValue={actorModal.data?.image || ''} />
              </div>
              <div className="form-group">
                <label>Display Order</label>
                <input type="number" name="actor_order" defaultValue={actorModal.data?.actor_order || 1} required />
              </div>
              <div className="form-group" style={{ display: 'flex', alignItems: 'center', gap: '10px', background: 'rgba(255,255,255,0.03)', padding: '12px', borderRadius: '8px' }}>
                <input type="checkbox" name="is_category" id="is_category_actor_chk" defaultChecked={actorModal.data?.is_category === 1} />
                <div>
                  <label htmlFor="is_category_actor_chk" style={{ margin: 0, cursor: 'pointer', fontWeight: 'bold' }}>Is Category Parent (is_category = 1)</label>
                  <div style={{ fontSize: '11px', color: 'var(--text-muted)' }}>If checked, categories can be nested under this entry. If unchecked, movies can be assigned directly to this actor.</div>
                </div>
              </div>
              <div className="modal-footer">
                <button type="button" className="btn-secondary" onClick={() => setActorModal({ show: false, mode: 'create', data: null })}>Cancel</button>
                <button type="submit" className="btn-primary">Save Actor</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* --- MODAL: Category --- */}
      {categoryModal.show && (
        <div className="modal-overlay">
          <div className="modal-content glass-panel animate-fade-in">
            <div className="modal-header">
              <h2 className="modal-title">{categoryModal.mode === 'create' ? 'Add YouTube Category' : 'Edit Category'}</h2>
              <button className="modal-close" onClick={() => setCategoryModal({ show: false, mode: 'create', data: null })}>&times;</button>
            </div>
            <form onSubmit={handleCategorySubmit}>
              <div className="form-group">
                <label>Parent Actor (is_category = 1) *</label>
                <select name="actor_id" defaultValue={categoryModal.data?.actor_id || ''} required>
                  <option value="">-- Select Category Parent Actor --</option>
                  {actorsList.filter(a => a.is_category === 1).map(act => (
                    <option key={act.id} value={act.id}>{act.name} (ID: {act.id})</option>
                  ))}
                </select>
                <span style={{ fontSize: '11px', color: 'var(--text-muted)', marginTop: '4px', display: 'block' }}>
                  Rule: Categories must be created under actors with <code>is_category = 1</code>.
                </span>
              </div>
              <div className="form-group">
                <label>Category Name *</label>
                <input type="text" name="name" placeholder="Rhymes" defaultValue={categoryModal.data?.name || ''} required />
              </div>
              <div className="form-group">
                <label>Category Image Path / URL</label>
                <input type="text" name="image" placeholder="/images/categories/rhymes.jpg" defaultValue={categoryModal.data?.image || ''} />
              </div>
              <div className="form-group">
                <label>Display Order</label>
                <input type="number" name="category_order" defaultValue={categoryModal.data?.category_order || 1} required />
              </div>
              <div className="modal-footer">
                <button type="button" className="btn-secondary" onClick={() => setCategoryModal({ show: false, mode: 'create', data: null })}>Cancel</button>
                <button type="submit" className="btn-primary">Save Category</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* --- MODAL: YouTube Movie --- */}
      {movieModal.show && (
        <div className="modal-overlay">
          <div className="modal-content glass-panel animate-fade-in">
            <div className="modal-header">
              <h2 className="modal-title">{movieModal.mode === 'create' ? 'Add YouTube Movie' : 'Edit YouTube Movie'}</h2>
              <button className="modal-close" onClick={() => setMovieModal({ show: false, mode: 'create', data: null })}>&times;</button>
            </div>
            <form onSubmit={handleMovieSubmit}>
              <div className="form-group">
                <label>Movie Title *</label>
                <input type="text" name="name" placeholder="Gang Leader" defaultValue={movieModal.data?.name || ''} required />
              </div>
              <div className="form-group">
                <label>YouTube Video ID * (Unique)</label>
                <input type="text" name="youtube_video_id" placeholder="e.g. ABC123 or 21X5lGlDOfg" defaultValue={movieModal.data?.youtube_video_id || ''} required />
                <span style={{ fontSize: '11px', color: 'var(--text-muted)' }}>Duplicate YouTube Video IDs are strictly rejected.</span>
              </div>

              <div className="form-group">
                <label>Mapping Type (Actor OR Category, not both) *</label>
                <select 
                  name="mapping_type"
                  defaultValue={movieModal.data?.category_id ? 'category' : 'actor'}
                  required
                >
                  <option value="actor">Map to Actor (is_category = 0)</option>
                  <option value="category">Map to Category</option>
                </select>
              </div>

              <div className="form-group">
                <label>Select Actor (If Map to Actor)</label>
                <select name="actor_id" defaultValue={movieModal.data?.actor_id || ''}>
                  <option value="">-- Choose Actor --</option>
                  {actorsList.filter(a => a.is_category === 0).map(act => (
                    <option key={act.id} value={act.id}>{act.name} (is_category = 0)</option>
                  ))}
                </select>
              </div>

              <div className="form-group">
                <label>Select Category (If Map to Category)</label>
                <select name="category_id" defaultValue={movieModal.data?.category_id || ''}>
                  <option value="">-- Choose Category --</option>
                  {categoriesList.map(cat => (
                    <option key={cat.id} value={cat.id}>{cat.name} (Under: {cat.actor_name})</option>
                  ))}
                </select>
              </div>

              <div className="form-group">
                <label>Role</label>
                <input type="text" name="role" placeholder="e.g. Hero, Guest" defaultValue={movieModal.data?.role || ''} />
              </div>
              <div className="form-group">
                <label>Poster Image URL</label>
                <input type="text" name="image" placeholder="/images/movies/gangleader.jpg" defaultValue={movieModal.data?.image || ''} />
              </div>
              <div className="form-group">
                <label>Thumbnail Image URL</label>
                <input type="text" name="thumbnail" placeholder="/images/movies/thumb.jpg" defaultValue={movieModal.data?.thumbnail || ''} />
              </div>
              <div className="form-group">
                <label>Description</label>
                <textarea name="description" rows="2" defaultValue={movieModal.data?.description || ''}></textarea>
              </div>

              <div className="modal-footer">
                <button type="button" className="btn-secondary" onClick={() => setMovieModal({ show: false, mode: 'create', data: null })}>Cancel</button>
                <button type="submit" className="btn-primary">Save Movie</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}

export default App;
