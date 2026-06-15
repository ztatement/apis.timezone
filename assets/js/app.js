
/**
  * Zentrale Funktion für die API-Aufrufe
  *
  * @param {string} endpoint - Der API-Endpunkt (z.B. 'convert')
  * @param {Object} params - Query-Parameter für den Request
  * @returns {Promise<void>}
  */
  async function callApi(endpoint, params = {}) {
    // Globale Variablen für die Latenz-Visualisierung und Request-Zähler
    if (typeof window.latencyData === 'undefined') window.latencyData = [];
    if (typeof window.requestCounter === 'undefined') window.requestCounter = 0;

    const output = document.getElementById('demoOutput');
    const statusInfo = document.getElementById('statusInfo');
    const apiKey = document.getElementById('apiKey').value;

    const queryString = new URLSearchParams(params).toString();
    const url = `./${endpoint}${queryString ? '?' + queryString : ''}`;

    output.textContent = `Sende Request an: ${url} ...`;
    statusInfo.innerHTML = '';

    try {
      const startTime = performance.now();
      const options = {
        headers: {}
      };

      if (apiKey) {
        options.headers['X-API-KEY'] = apiKey;
      }

      const response = await fetch(url, options);
      const duration = Math.round(performance.now() - startTime);
      window.requestCounter++;
      window.latencyData.push({
        index: window.requestCounter,
        duration: duration,
        type: endpoint
      });
      const contentType = response.headers.get("content-type");

      if (!contentType || !contentType.includes("application/json")) {
        const text = await response.text();
        throw new Error(`Server hat kein JSON geantwortet, sondern: ${contentType}\n\nAntwort-Vorschau:\n${text.substring(0, 500)}`);
      }

      const data = await response.json();
      output.textContent = JSON.stringify(data, null, 4);

      if (!response.ok) {
        statusInfo.innerHTML = `<span class="error">Status: ${response.status} ${response.statusText}</span> <small style="color: #888; margin-left: 8px;">(${duration}ms)</small>`;
      } else {
        statusInfo.innerHTML = `<span style="color: #4caf50;">Status: 200 OK</span> <small style="color: #888; margin-left: 8px;">(${duration}ms)</small>`;
        if (data.day_jump) {
          statusInfo.innerHTML += `<span class="warning-box" style="color: #ffa500; font-weight: bold; margin-left: 10px;">⚠️ Hinweis: Datumswechsel (Tagessprung)!</span>`;
        }
      }
      updateLatencyChart();
    } catch (err) {
      output.innerHTML = `<span class="error">FEHLER:</span>\n${err.message}`;
    }
  }

/**
  * Aktualisiert den 'Disabled'-Status des Senden-Buttons basierend auf der Feld-Validierung.
  */
  function updateSubmitButtonState() {
    const action = document.getElementById('apiAction').value;
    const btn = document.getElementById('submitBtn');

    if (action === 'convert') {
      const isInvalid = document.getElementById('fromTz').classList.contains('invalid') ||
        document.getElementById('toTz').classList.contains('invalid') ||
        document.getElementById('timeInput').classList.contains('invalid');
      btn.disabled = isInvalid;
    } else {
      btn.disabled = false;
    }
  }

/**
  * Korrigiert die Groß-/Kleinschreibung einer Zeitzone automatisch,
  * wenn ein Case-Insensitive Match in der Liste gefunden wird.
  * 
  * @param {HTMLInputElement} el - Das Input-Element.
  */
  function fixTimezoneCasing(el) {
    if (validTimezones.length === 0) return;
    const lowerInput = el.value.toLowerCase().trim();
    const match = validTimezones.find(tz => tz.toLowerCase() === lowerInput);
    if (match && match !== el.value) {
      el.value = match;
      validateTimezoneInput(el);
    }
  }

/**
  * Prüft, ob der eingegebene Text eine gültige IANA-Zeitzone ist.
  * 
  * @param {HTMLInputElement} el - Das zu validierende Element.
  */
  function validateTimezoneInput(el) {
    const errorEl = document.getElementById(el.id + 'Error');
    const val = el.value.trim().toLowerCase();
    if (val === "" || validTimezones.length === 0) {
      el.classList.remove('invalid');
      errorEl.textContent = "";
    } else if (!validTimezones.some(tz => tz.toLowerCase() === val)) {
      el.classList.add('invalid');
      errorEl.textContent = "⚠️ Ungültige Zeitzone";
    } else {
      el.classList.remove('invalid');
      errorEl.textContent = "";
    }
    updateSubmitButtonState();
  }

/**
  * Prüft, ob das Datum gültig ist und (optional) nicht in der Vergangenheit liegt.
  * 
  * @param {HTMLInputElement} el - Das Zeit-Input Element.
  */
  function validateTimeInput(el) {
    const errorEl = document.getElementById('timeInputError');
    const allowPast = document.getElementById('allowPast').checked;

    if (!el.value) {
      el.classList.add('invalid');
      errorEl.textContent = "⚠️ Bitte Zeit angeben";
    } else if (!allowPast) {
      const now = new Date();
      const offset = now.getTimezoneOffset() * 60000;
      const currentMinuteStr = new Date(now.getTime() - offset).toISOString().slice(0, 16);

      if (el.value < currentMinuteStr) {
        el.classList.add('invalid');
        errorEl.textContent = "⚠️ Zeit darf nicht in der Vergangenheit liegen";
      } else {
        el.classList.remove('invalid');
        errorEl.textContent = "";
      }
    } else {
      el.classList.remove('invalid');
      errorEl.textContent = "";
    }
    updateSubmitButtonState();
  }

/**
  * Passt die Sichtbarkeit der Input-Felder je nach gewählter Aktion an.
  */
  function toggleInputs() {
    const action = document.getElementById('apiAction').value;
    const isConvert = action === 'convert';
    const isTransitions = action === 'transitions';
    
    document.getElementById('convertInputs').style.display = (isConvert || isTransitions) ? 'flex' : 'none';
    document.getElementById('toTz').parentElement.style.display = isTransitions ? 'none' : 'flex';
    document.querySelector('button[onclick="swapTimezones()"]').style.display = isTransitions ? 'none' : 'block';
    document.getElementById('timeInput').parentElement.parentElement.style.display = isTransitions ? 'none' : 'flex';
    
    updateSubmitButtonState();
  }

/**
  * Sammelt die Formulardaten und löst den callApi-Prozess aus.
  */
  function runDemo() {
    const action = document.getElementById('apiAction').value;
    if (action === 'convert') {
      const fromEl = document.getElementById('fromTz');
      const toEl = document.getElementById('toTz');
      fixTimezoneCasing(fromEl);
      fixTimezoneCasing(toEl);
      validateTimeInput(document.getElementById('timeInput'));
      if (document.getElementById('submitBtn').disabled) return;
      callApi('convert', {
        from: fromEl.value,
        to: toEl.value,
        time: document.getElementById('timeInput').value
      });
    } else if (action === 'transitions') {
      const tzEl = document.getElementById('fromTz');
      callApi('transitions', { tz: tzEl.value });
    } else {
      callApi(action);
    }
  }

/**
  * Schaltet das 'min'-Attribut für das Zeitfeld basierend auf der Checkbox um.
  */
  function togglePastDates() {
    const timeInput = document.getElementById('timeInput');
    const allowPast = document.getElementById('allowPast').checked;
    if (allowPast) {
      timeInput.removeAttribute('min');
    } else {
      const now = new Date();
      const offset = now.getTimezoneOffset() * 60000;
      const localISOTime = new Date(now.getTime() - offset).toISOString().slice(0, 16);
      timeInput.min = localISOTime;
    }
    validateTimeInput(timeInput);
  }

/**
  * Setzt das Zeitfeld auf die aktuelle Minute des Nutzers.
  */
  function setDateTimeInputToCurrentMinute() {
    const now = new Date();
    const offset = now.getTimezoneOffset() * 60000;
    const localISOTime = new Date(now.getTime() - offset).toISOString().slice(0, 16);
    const timeInput = document.getElementById('timeInput');
    timeInput.value = localISOTime;
    togglePastDates();
  }

/**
  * Tauscht die Werte von Start- und Zielzeitzone.
  */
  function swapTimezones() {
    const fromInput = document.getElementById('fromTz');
    const toInput = document.getElementById('toTz');
    const temp = fromInput.value;
    fromInput.value = toInput.value;
    toInput.value = temp;
    validateTimezoneInput(fromInput);
    validateTimezoneInput(toInput);
  }

/**
  * Versucht die Zeitzone des Browsers automatisch zu erkennen.
  */
  function detectUserTimezone() {
    try {
      const userTz = Intl.DateTimeFormat().resolvedOptions().timeZone;
      if (userTz) {
        document.getElementById('fromTz').value = userTz;
      }
      setDateTimeInputToCurrentMinute();
    } catch (e) {
      console.warn("Automatische Zeitzonenerkennung fehlgeschlagen:", e);
    }
  }

/**
  * Kopiert die API-URL in die Zwischenablage und hängt den aktuellen Key an.
  * 
  * @param {string} text - Die Basis-URL.
  * @param {HTMLButtonElement} btn - Der geklickte Button für Feedback.
  */
  function copyToClipboard(text, btn) {
    const apiKey = document.getElementById('apiKey').value;
    let finalUrl = text;
    if (apiKey) {
      finalUrl += (finalUrl.includes('?') ? '&' : '?') + 'key=' + encodeURIComponent(apiKey);
    }
    navigator.clipboard.writeText(finalUrl).then(() => {
      const originalText = btn.textContent;
      btn.textContent = "Kopiert!";
      btn.style.background = "#28a745";
      setTimeout(() => {
        btn.textContent = originalText;
        btn.style.background = "";
      }, 2000);
    }).catch(err => {
      console.error('Fehler beim Kopieren:', err);
    });
  }

/**
  * Füllt die Konsole mit Werten aus einem Dokumentations-Beispiel.
  * 
  * @param {string} action - Die API-Aktion.
  * @param {string} from - Start-Zeitzone.
  * @param {string} to - Ziel-Zeitzone.
  * @param {string} time - ISO-Zeit.
  */
  function loadExample(action, from = '', to = '', time = '') {
    const actionSelect = document.getElementById('apiAction');
    actionSelect.value = action;
    toggleInputs();
    if (action === 'convert') {
      const f = document.getElementById('fromTz');
      const t = document.getElementById('toTz');
      f.value = from;
      t.value = to;
      document.getElementById('timeInput').value = time;
      validateTimezoneInput(f);
      validateTimezoneInput(t);
      validateTimeInput(document.getElementById('timeInput'));
    }
    document.getElementById('demo-area').scrollIntoView({ behavior: 'smooth' });
  }

  let validTimezones = []; // Cache für die Validierung
/**
  * Lädt die Liste aller Zeitzonen (mit Caching im LocalStorage),
  * um das Datalist-Dropdown und die Validierung zu füttern.
  */
  async function populateTimezones() {
    const CACHE_KEY = 'tz_list_cache';
    const CACHE_EXPIRY = 24 * 60 * 60 * 1000;
    const datalist = document.getElementById('tzOptions');
    const fillDatalist = (timezones) => {
      datalist.innerHTML = '';
      validTimezones = timezones;
      timezones.forEach(tz => {
        const option = document.createElement('option');
        option.value = tz;
        datalist.appendChild(option);
      });
    };
    const cached = JSON.parse(localStorage.getItem(CACHE_KEY));
    if (cached && (Date.now() - cached.timestamp < CACHE_EXPIRY)) {
      fillDatalist(cached.data);
      return;
    }
    try {
      const response = await fetch('./timezones/all');
      if (response.ok) {
        const timezones = await response.json();
        localStorage.setItem(CACHE_KEY, JSON.stringify({ timestamp: Date.now(), data: timezones }));
        fillDatalist(timezones);
      }
    } catch (e) {
      console.warn("Konnte Zeitzonenliste nicht für das Dropdown laden:", e);
    }
  }

/**
  * Blendet das Performance-Diagramm ein oder aus.
  */
  function toggleChart() {
    const container = document.getElementById('latencyChartContainer');
    const btn = document.getElementById('toggleChartBtn');
    if (container.style.display === 'none' || container.style.display === '') {
      container.style.display = 'block';
      btn.textContent = 'Performance-Statistiken ausblenden';
    } else {
      container.style.display = 'none';
      btn.textContent = 'Performance-Statistiken anzeigen';
    }
  }

  let latencyChartInstance = null;
  const MAX_LATENCY_POINTS = 10;
/**
  * Rendert oder aktualisiert das Chart.js Diagramm mit den neuesten Latenz-Werten.
  */
  function updateLatencyChart() {
    const ctx = document.getElementById('latencyChart').getContext('2d');
    const latencyData = window.latencyData || [];
    if (latencyData.length > MAX_LATENCY_POINTS) {
      latencyData.shift();
    }
    const avg = latencyData.length > 0 
      ? Math.round(latencyData.reduce((acc, curr) => acc + curr.duration, 0) / latencyData.length) 
      : 0;
    document.getElementById('avgLatency').textContent = avg > 0 ? `(Ø ${avg}ms)` : '';
    const labels = latencyData.map((_, i) => `Anfrage ${i + 1}`);
    if (latencyChartInstance) {
      latencyChartInstance.data.labels = labels;
      latencyChartInstance.data.datasets[0].data = latencyData.map(d => d.duration);
      latencyChartInstance.update();
    } else {
      latencyChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [{
            label: 'Antwortzeit (ms)',
            data: latencyData.map(d => d.duration),
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1,
            fill: false
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Millisekunden' } },
            x: { title: { display: true, text: 'Anfrage' } }
          }
        }
      });
    }
  }

/**
  * Initialisierung aller Event-Listener und Daten beim Laden der Seite.
  */
  window.onload = () => {
    toggleInputs();
    detectUserTimezone();
    populateTimezones();
    document.getElementById('fromTz').addEventListener('input', (e) => validateTimezoneInput(e.target));
    document.getElementById('toTz').addEventListener('input', (e) => validateTimezoneInput(e.target));
    document.getElementById('timeInput').addEventListener('input', (e) => validateTimeInput(e.target));
    document.getElementById('fromTz').addEventListener('blur', (e) => fixTimezoneCasing(e.target));
    document.getElementById('toTz').addEventListener('blur', (e) => fixTimezoneCasing(e.target));
    updateLatencyChart();
  };
