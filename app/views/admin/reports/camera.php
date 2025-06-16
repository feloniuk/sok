<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Відеоспостереження - Панель адміністратора</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .surveillance-card {
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border: none;
            margin-bottom: 20px;
        }
        
        .camera-container {
            position: relative;
            background: #000;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .camera-video {
            width: 100%;
            height: 500px;
            object-fit: cover;
            border-radius: 0.5rem;
        }
        
        .camera-placeholder {
            width: 100%;
            height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            flex-direction: column;
            border-radius: 0.5rem;
        }
        
        .camera-controls {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 10px;
            z-index: 10;
        }
        
        .camera-status {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 10;
        }
        
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
            animation: pulse 2s infinite;
        }
        
        .status-online {
            background-color: #28a745;
        }
        
        .status-offline {
            background-color: #dc3545;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .control-btn {
            background: rgba(0,0,0,0.7);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .control-btn:hover {
            background: rgba(0,0,0,0.9);
            transform: scale(1.1);
        }
        
        .fullscreen-btn {
            background: #007bff;
        }
        
        .fullscreen-btn:hover {
            background: #0056b3;
        }
        
        .record-btn {
            background: #dc3545;
        }
        
        .record-btn:hover {
            background: #c82333;
        }
        
        .record-btn.recording {
            animation: recordPulse 1s infinite;
        }
        
        @keyframes recordPulse {
            0% { background-color: #dc3545; }
            50% { background-color: #ff4d6d; }
            100% { background-color: #dc3545; }
        }
        
        .camera-info {
            padding: 15px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        
        .error-message {
            color: #dc3545;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 0.25rem;
            padding: 10px;
            margin: 10px 0;
        }
        
        .timestamp {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Хлібні крихти -->
        <div class="row mb-4">
            <div class="col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#"><i class="fas fa-home"></i> Головна</a></li>
                        <li class="breadcrumb-item"><a href="#">Адміністрування</a></li>
                        <li class="breadcrumb-item active">Відеоспостереження</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Заголовок -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="h2 mb-0">
                    <i class="fas fa-video text-primary me-2"></i>
                    Система відеоспостереження
                </h1>
                <p class="text-muted">Перегляд камери в реальному часі</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group">
                    <button id="refreshBtn" class="btn btn-outline-primary">
                        <i class="fas fa-sync-alt me-1"></i> Оновити
                    </button>
                    <button id="settingsBtn" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#settingsModal">
                        <i class="fas fa-cog me-1"></i> Налаштування
                    </button>
                </div>
            </div>
        </div>

        <!-- Основна камера -->
        <div class="row">
            <div class="col-md-12">
                <div class="card surveillance-card">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-camera me-2"></i>
                            Основна камера
                        </h5>
                        <div class="camera-status">
                            <span class="status-indicator status-offline" id="statusIndicator"></span>
                            <span id="statusText">Офлайн</span>
                        </div>
                    </div>
                    
                    <div class="card-body p-0">
                        <div class="camera-container">
                            <!-- Відео елемент -->
                            <video id="cameraVideo" class="camera-video" autoplay muted style="display: none;"></video>
                            
                            <!-- Заглушка при відсутності камери -->
                            <div id="cameraPlaceholder" class="camera-placeholder">
                                <i class="fas fa-video-slash fa-5x mb-3 opacity-50"></i>
                                <h3>Камера недоступна</h3>
                                <p class="mb-3">Натисніть "Увімкнути камеру" для підключення</p>
                                <button id="startCameraBtn" class="btn btn-primary btn-lg">
                                    <i class="fas fa-play me-2"></i>Увімкнути камеру
                                </button>
                            </div>
                            
                            <!-- Елементи управління -->
                            <div class="camera-controls" id="cameraControls" style="display: none;">
                                <button class="control-btn fullscreen-btn" id="fullscreenBtn" title="Повноекранний режим">
                                    <i class="fas fa-expand"></i>
                                </button>
                                <button class="control-btn record-btn" id="recordBtn" title="Запис відео">
                                    <i class="fas fa-circle"></i>
                                </button>
                                <button class="control-btn" id="screenshotBtn" title="Зробити знімок">
                                    <i class="fas fa-camera"></i>
                                </button>
                                <button class="control-btn" id="stopCameraBtn" title="Зупинити камеру">
                                    <i class="fas fa-stop"></i>
                                </button>
                            </div>
                            
                            <!-- Мітка часу -->
                            <div class="timestamp" id="timestamp" style="display: none;"></div>
                            
                            <!-- Повідомлення про помилки -->
                            <div id="errorMessage" class="error-message" style="display: none;"></div>
                        </div>
                    </div>
                    
                    <div class="camera-info">
                        <div class="row">
                            <div class="col-md-3">
                                <small class="text-muted">Розширення:</small>
                                <div id="resolution">-</div>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">FPS:</small>
                                <div id="fps">-</div>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Статус запису:</small>
                                <div id="recordingStatus">Не записується</div>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Тривалість роботи:</small>
                                <div id="uptime">00:00:00</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Додаткові функції -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card surveillance-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-images me-2"></i>
                            Останні знімки
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="screenshotGallery" class="row g-2">
                            <div class="col-12 text-center text-muted">
                                <i class="fas fa-camera fa-2x mb-2"></i>
                                <p>Знімки з'являться тут після їх створення</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card surveillance-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-video me-2"></i>
                            Записи
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="recordingsList">
                            <div class="text-center text-muted">
                                <i class="fas fa-video fa-2x mb-2"></i>
                                <p>Записи з'являться тут після їх створення</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Модальне вікно налаштувань -->
    <div class="modal fade" id="settingsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Налаштування камери</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="videoQuality" class="form-label">Якість відео</label>
                        <select class="form-select" id="videoQuality">
                            <option value="1280x720">HD (1280x720)</option>
                            <option value="1920x1080">Full HD (1920x1080)</option>
                            <option value="640x480">SD (640x480)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="frameRate" class="form-label">Кадрова частота</label>
                        <select class="form-select" id="frameRate">
                            <option value="15">15 FPS</option>
                            <option value="24">24 FPS</option>
                            <option value="30" selected>30 FPS</option>
                            <option value="60">60 FPS</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="autoRecord">
                        <label class="form-check-label" for="autoRecord">
                            Автоматичний запис при виявленні руху
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Скасувати</button>
                    <button type="button" class="btn btn-primary" id="saveSettings">Зберегти</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        class SurveillanceSystem {
            constructor() {
                this.videoElement = document.getElementById('cameraVideo');
                this.placeholder = document.getElementById('cameraPlaceholder');
                this.stream = null;
                this.mediaRecorder = null;
                this.recordedChunks = [];
                this.isRecording = false;
                this.startTime = null;
                this.screenshots = [];
                this.recordings = [];
                
                this.initializeElements();
                this.bindEvents();
                this.startTimer();
            }
            
            initializeElements() {
                this.startBtn = document.getElementById('startCameraBtn');
                this.stopBtn = document.getElementById('stopCameraBtn');
                this.recordBtn = document.getElementById('recordBtn');
                this.screenshotBtn = document.getElementById('screenshotBtn');
                this.fullscreenBtn = document.getElementById('fullscreenBtn');
                this.controls = document.getElementById('cameraControls');
                this.statusIndicator = document.getElementById('statusIndicator');
                this.statusText = document.getElementById('statusText');
                this.timestamp = document.getElementById('timestamp');
                this.errorMessage = document.getElementById('errorMessage');
                this.resolution = document.getElementById('resolution');
                this.fpsDisplay = document.getElementById('fps');
                this.recordingStatus = document.getElementById('recordingStatus');
                this.uptime = document.getElementById('uptime');
            }
            
            bindEvents() {
                this.startBtn.addEventListener('click', () => this.startCamera());
                this.stopBtn.addEventListener('click', () => this.stopCamera());
                this.recordBtn.addEventListener('click', () => this.toggleRecording());
                this.screenshotBtn.addEventListener('click', () => this.takeScreenshot());
                this.fullscreenBtn.addEventListener('click', () => this.toggleFullscreen());
                
                document.getElementById('refreshBtn').addEventListener('click', () => this.refreshCamera());
                document.getElementById('saveSettings').addEventListener('click', () => this.saveSettings());
                
                // Обробка помилок відео
                this.videoElement.addEventListener('error', (e) => {
                    this.showError('Помилка відтворення відео: ' + e.message);
                });
            }
            
            async startCamera() {
                try {
                    this.hideError();
                    this.startBtn.disabled = true;
                    this.startBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Підключення...';
                    
                    const constraints = {
                        video: {
                            width: { ideal: 1280 },
                            height: { ideal: 720 },
                            frameRate: { ideal: 30 }
                        },
                        audio: false
                    };
                    
                    this.stream = await navigator.mediaDevices.getUserMedia(constraints);
                    this.videoElement.srcObject = this.stream;
                    
                    // Показуємо відео та ховаємо заглушку
                    this.videoElement.style.display = 'block';
                    this.placeholder.style.display = 'none';
                    this.controls.style.display = 'block';
                    this.timestamp.style.display = 'block';
                    
                    // Оновлюємо статус
                    this.updateStatus(true);
                    this.startTime = Date.now();
                    
                    // Отримуємо інформацію про відео
                    this.videoElement.addEventListener('loadedmetadata', () => {
                        this.updateVideoInfo();
                    });
                    
                    console.log('Камера успішно підключена');
                    
                } catch (error) {
                    console.error('Помилка доступу до камери:', error);
                    this.showError(this.getErrorMessage(error));
                    this.startBtn.disabled = false;
                    this.startBtn.innerHTML = '<i class="fas fa-play me-2"></i>Увімкнути камеру';
                }
            }
            
            stopCamera() {
                if (this.stream) {
                    // Зупиняємо всі треки
                    this.stream.getTracks().forEach(track => track.stop());
                    this.stream = null;
                }
                
                if (this.isRecording) {
                    this.stopRecording();
                }
                
                // Ховаємо відео та показуємо заглушку
                this.videoElement.style.display = 'none';
                this.placeholder.style.display = 'flex';
                this.controls.style.display = 'none';
                this.timestamp.style.display = 'none';
                
                // Оновлюємо статус
                this.updateStatus(false);
                this.startTime = null;
                
                // Повертаємо кнопку запуску
                this.startBtn.disabled = false;
                this.startBtn.innerHTML = '<i class="fas fa-play me-2"></i>Увімкнути камеру';
                
                console.log('Камера зупинена');
            }
            
            toggleRecording() {
                if (this.isRecording) {
                    this.stopRecording();
                } else {
                    this.startRecording();
                }
            }
            
            async startRecording() {
                try {
                    if (!this.stream) {
                        this.showError('Спочатку увімкніть камеру');
                        return;
                    }
                    
                    this.recordedChunks = [];
                    this.mediaRecorder = new MediaRecorder(this.stream, {
                        mimeType: 'video/webm; codecs=vp9'
                    });
                    
                    this.mediaRecorder.ondataavailable = (event) => {
                        if (event.data.size > 0) {
                            this.recordedChunks.push(event.data);
                        }
                    };
                    
                    this.mediaRecorder.onstop = () => {
                        this.saveRecording();
                    };
                    
                    this.mediaRecorder.start();
                    this.isRecording = true;
                    
                    // Оновлюємо UI
                    this.recordBtn.classList.add('recording');
                    this.recordBtn.innerHTML = '<i class="fas fa-square"></i>';
                    this.recordBtn.title = 'Зупинити запис';
                    this.recordingStatus.textContent = 'Записується';
                    
                    console.log('Запис розпочато');
                    
                } catch (error) {
                    console.error('Помилка запису:', error);
                    this.showError('Помилка запуску запису: ' + error.message);
                }
            }
            
            stopRecording() {
                if (this.mediaRecorder && this.isRecording) {
                    this.mediaRecorder.stop();
                    this.isRecording = false;
                    
                    // Оновлюємо UI
                    this.recordBtn.classList.remove('recording');
                    this.recordBtn.innerHTML = '<i class="fas fa-circle"></i>';
                    this.recordBtn.title = 'Запис відео';
                    this.recordingStatus.textContent = 'Не записується';
                    
                    console.log('Запис зупинено');
                }
            }
            
            saveRecording() {
                const blob = new Blob(this.recordedChunks, { type: 'video/webm' });
                const url = URL.createObjectURL(blob);
                const timestamp = new Date().toLocaleString('uk-UA');
                
                // Зберігаємо запис
                const recording = {
                    url: url,
                    timestamp: timestamp,
                    size: this.formatFileSize(blob.size)
                };
                
                this.recordings.unshift(recording);
                this.updateRecordingsList();
                
                // Створюємо посилання для завантаження
                const a = document.createElement('a');
                a.href = url;
                a.download = `surveillance_${Date.now()}.webm`;
                a.click();
            }
            
            takeScreenshot() {
                if (!this.stream) {
                    this.showError('Спочатку увімкніть камеру');
                    return;
                }
                
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                canvas.width = this.videoElement.videoWidth;
                canvas.height = this.videoElement.videoHeight;
                
                ctx.drawImage(this.videoElement, 0, 0);
                
                canvas.toBlob((blob) => {
                    const url = URL.createObjectURL(blob);
                    const timestamp = new Date().toLocaleString('uk-UA');
                    
                    // Зберігаємо знімок
                    const screenshot = {
                        url: url,
                        timestamp: timestamp,
                        size: this.formatFileSize(blob.size)
                    };
                    
                    this.screenshots.unshift(screenshot);
                    this.updateScreenshotGallery();
                    
                    // Створюємо посилання для завантаження
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `screenshot_${Date.now()}.png`;
                    a.click();
                    
                    console.log('Знімок зроблено');
                }, 'image/png');
            }
            
            toggleFullscreen() {
                if (!document.fullscreenElement) {
                    this.videoElement.requestFullscreen().catch(err => {
                        console.error('Помилка повноекранного режиму:', err);
                    });
                } else {
                    document.exitFullscreen();
                }
            }
            
            refreshCamera() {
                if (this.stream) {
                    this.stopCamera();
                    setTimeout(() => this.startCamera(), 1000);
                }
            }
            
            updateStatus(online) {
                if (online) {
                    this.statusIndicator.className = 'status-indicator status-online';
                    this.statusText.textContent = 'Онлайн';
                } else {
                    this.statusIndicator.className = 'status-indicator status-offline';
                    this.statusText.textContent = 'Офлайн';
                }
            }
            
            updateVideoInfo() {
                if (this.videoElement.videoWidth && this.videoElement.videoHeight) {
                    this.resolution.textContent = `${this.videoElement.videoWidth}x${this.videoElement.videoHeight}`;
                    this.fpsDisplay.textContent = '30'; // Приблизно
                }
            }
            
            updateScreenshotGallery() {
                const gallery = document.getElementById('screenshotGallery');
                
                if (this.screenshots.length === 0) {
                    gallery.innerHTML = `
                        <div class="col-12 text-center text-muted">
                            <i class="fas fa-camera fa-2x mb-2"></i>
                            <p>Знімки з'являться тут після їх створення</p>
                        </div>
                    `;
                    return;
                }
                
                gallery.innerHTML = this.screenshots.slice(0, 6).map(screenshot => `
                    <div class="col-md-4 mb-2">
                        <div class="card">
                            <img src="${screenshot.url}" class="card-img-top" style="height: 100px; object-fit: cover;">
                            <div class="card-body p-2">
                                <small class="text-muted">${screenshot.timestamp}</small>
                                <br>
                                <small class="text-muted">${screenshot.size}</small>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
            
            updateRecordingsList() {
                const list = document.getElementById('recordingsList');
                
                if (this.recordings.length === 0) {
                    list.innerHTML = `
                        <div class="text-center text-muted">
                            <i class="fas fa-video fa-2x mb-2"></i>
                            <p>Записи з'являться тут після їх створення</p>
                        </div>
                    `;
                    return;
                }
                
                list.innerHTML = this.recordings.slice(0, 5).map(recording => `
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                        <div>
                            <div><i class="fas fa-video me-2"></i>Запис від ${recording.timestamp}</div>
                            <small class="text-muted">Розмір: ${recording.size}</small>
                        </div>
                        <a href="${recording.url}" download class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                `).join('');
            }
            
            startTimer() {
                setInterval(() => {
                    // Оновлюємо мітку часу
                    const now = new Date();
                    this.timestamp.textContent = now.toLocaleString('uk-UA');
                    
                    // Оновлюємо час роботи
                    if (this.startTime) {
                        const uptime = Date.now() - this.startTime;
                        this.uptime.textContent = this.formatUptime(uptime);
                    } else {
                        this.uptime.textContent = '00:00:00';
                    }
                }, 1000);
            }
            
            formatUptime(ms) {
                const seconds = Math.floor(ms / 1000);
                const hours = Math.floor(seconds / 3600);
                const minutes = Math.floor((seconds % 3600) / 60);
                const secs = seconds % 60;
                
                return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            }
            
            formatFileSize(bytes) {
                const sizes = ['Б', 'КБ', 'МБ', 'ГБ'];
                if (bytes === 0) return '0 Б';
                const i = Math.floor(Math.log(bytes) / Math.log(1024));
                return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
            }
            
            getErrorMessage(error) {
                if (error.name === 'NotAllowedError') {
                    return 'Доступ до камери заборонено. Будь ласка, дозвольте доступ до камери в налаштуваннях браузера.';
                } else if (error.name === 'NotFoundError') {
                    return 'Камера не знайдена. Переконайтеся, що камера підключена до комп\'ютера.';
                } else if (error.name === 'NotReadableError') {
                    return 'Камера зайнята іншою програмою. Закрийте інші програми, що використовують камеру.';
                } else {
                    return 'Помилка доступу до камери: ' + error.message;
                }
            }
            
            showError(message) {
                this.errorMessage.textContent = message;
                this.errorMessage.style.display = 'block';
                setTimeout(() => this.hideError(), 5000);
            }
            
            hideError() {
                this.errorMessage.style.display = 'none';
            }
            
            saveSettings() {
                const quality = document.getElementById('videoQuality').value;
                const frameRate = document.getElementById('frameRate').value;
                const autoRecord = document.getElementById('autoRecord').checked;
                
                console.log('Налаштування збережено:', { quality, frameRate, autoRecord });
                
                // Закриваємо модальне вікно
                const modal = bootstrap.Modal.getInstance(document.getElementById('settingsModal'));
                modal.hide();
                
                // Показуємо повідомлення про успіх
                this.showSuccess('Налаштування успішно збережено');
            }
            
            showSuccess(message) {
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
                alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                alert.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(alert);
                
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 3000);
            }
        }
        
        // Ініціалізація системи після завантаження сторінки
        document.addEventListener('DOMContentLoaded', function() {
            // Перевіряємо підтримку браузером
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                document.body.innerHTML = `
                    <div class="container mt-5">
                        <div class="alert alert-danger">
                            <h4>Браузер не підтримується</h4>
                            <p>Ваш браузер не підтримує доступ до камери. Будь ласка, використовуйте сучасний браузер як Google Chrome, Firefox або Safari.</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            // Ініціалізуємо систему відеоспостереження
            new SurveillanceSystem();
            
            console.log('Система відеоспостереження ініціалізована');
        });
        
        // Обробка помилок JavaScript
        window.addEventListener('error', function(e) {
            console.error('JavaScript помилка:', e.error);
        });
        
        // Попередження про закриття сторінки під час запису
        window.addEventListener('beforeunload', function(e) {
            const recordBtn = document.getElementById('recordBtn');
            if (recordBtn && recordBtn.classList.contains('recording')) {
                e.preventDefault();
                e.returnValue = 'Зараз відбувається запис відео. Ви впевнені, що хочете закрити сторінку?';
            }
        });
    </script>
</body>
</html>