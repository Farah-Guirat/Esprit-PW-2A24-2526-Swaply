# 🚀 Guide de Déploiement Production - Appels Vidéo Swaply

## 📋 Checklist Avant Production

### Sécurité
- [ ] HTTPS/SSL configuré
- [ ] WSS (WebSocket Secure) pour Socket.io
- [ ] CORS strictement configuré
- [ ] Authentification robuste
- [ ] Validation des données côté serveur
- [ ] Rate limiting sur les API
- [ ] Logs de sécurité activés
- [ ] Certificats SSL à jour

### Performance
- [ ] TURN server configuré
- [ ] CDN pour les assets statiques
- [ ] Compression GZIP activée
- [ ] Cache navigateur configuré
- [ ] Base de données optimisée
- [ ] Indices BDD créés
- [ ] Monitoring de latence
- [ ] Tests de charge effectués

### Infrastructure
- [ ] Serveur Node.js dédié ou cluster
- [ ] PM2 ou similaire pour relance
- [ ] Reverse proxy (nginx/Apache)
- [ ] Load balancing si nécessaire
- [ ] Sauvegarde BDD régulière
- [ ] Monitoring serveur
- [ ] Logs centralisés

## 🔐 Configuration Sécurité

### 1. HTTPS/WSS

#### nginx.conf
```nginx
server {
    listen 443 ssl http2;
    server_name yourdomain.com;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    location / {
        proxy_pass http://localhost:8080;
    }

    location /socket.io {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
}
```

#### .env serveur Node.js
```env
PORT=3000
NODE_ENV=production
ALLOWED_ORIGINS=https://yourdomain.com,https://www.yourdomain.com

# HTTPS
USE_HTTPS=true
SSL_CERT=/path/to/cert.pem
SSL_KEY=/path/to/key.pem

# TURN server
TURN_SERVER=turn:turnserver.yourdomain.com:3478
TURN_USERNAME=turn_user
TURN_PASSWORD=secure_password

# Logging
LOG_LEVEL=info
```

### 2. CORS Configuration

```javascript
// video_server/server.js
const io = socketIo(server, {
    cors: {
        origin: [
            'https://yourdomain.com',
            'https://www.yourdomain.com'
        ],
        methods: ['GET', 'POST'],
        credentials: true,
        allowedHeaders: ['Content-Type', 'Authorization']
    }
});
```

### 3. Rate Limiting

```javascript
const rateLimit = require('express-rate-limit');

const limiter = rateLimit({
    windowMs: 15 * 60 * 1000, // 15 minutes
    max: 100,
    message: 'Trop de requêtes depuis cette IP'
});

app.use('/api/', limiter);
app.use('/controller/VideoCallController.php', limiter);
```

## ⚡ Optimisation Performance

### 1. TURN Server

Installer coturn:
```bash
sudo apt-get install coturn

# Configuration: /etc/coturn/turnserver.conf
listening-port=3478
listening-ip=0.0.0.0
realm=yourdomain.com
user=turn_user:secure_password
```

### 2. PM2 pour Node.js

```bash
npm install -g pm2

# ecosystem.config.js
module.exports = {
    apps: [{
        name: 'swaply-video-server',
        script: './server.js',
        instances: 'max',
        exec_mode: 'cluster',
        env: {
            NODE_ENV: 'production'
        }
    }]
};

# Lancer
pm2 start ecosystem.config.js
pm2 save
pm2 startup
```

### 3. Optimisation BDD

```sql
-- Ajouter des indices pour recherche rapide
ALTER TABLE video_calls 
ADD INDEX idx_user_date (id_initiateur, date_debut DESC);

ALTER TABLE video_call_participants 
ADD INDEX idx_user_status (id_user, statut_participant);

-- Archiver les anciens appels
CREATE TABLE video_calls_archive LIKE video_calls;

INSERT INTO video_calls_archive 
SELECT * FROM video_calls 
WHERE date_fin < DATE_SUB(NOW(), INTERVAL 90 DAY);

DELETE FROM video_calls 
WHERE date_fin < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

### 4. Compression et Cache

```php
// config.php
header('Content-Encoding: gzip');
header('Cache-Control: public, max-age=3600');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');

ob_start('ob_gzhandler');
```

## 📊 Monitoring et Logs

### 1. Logs Centralisés

Utiliser ELK Stack ou similaire:
```bash
# Rediriger logs vers syslog
echo "*.* @@localhost:514" >> /etc/rsyslog.conf

# ou utiliser Winston avec Elasticsearch
npm install winston winston-elasticsearch
```

### 2. Alertes

```javascript
// Monitoring basique
setInterval(async () => {
    const calls = activeCalls.size;
    const sockets = io.engine.clientsCount;
    
    if (calls > 1000) {
        sendAlert('Nombre élevé d\'appels: ' + calls);
    }
    
    if (sockets > 5000) {
        sendAlert('Trop de connexions: ' + sockets);
    }
}, 60000); // Chaque minute
```

### 3. Métriques Prometheus

```javascript
const prometheus = require('prom-client');

const callsMetric = new prometheus.Gauge({
    name: 'swaply_active_calls',
    help: 'Nombre d\'appels actifs'
});

setInterval(() => {
    callsMetric.set(activeCalls.size);
}, 5000);
```

## 🔄 Scalabilité

### Redis Adapter pour Socket.io

```javascript
const { createAdapter } = require('@socket.io/redis-adapter');
const { createClient } = require('redis');

const pubClient = createClient({ host: 'localhost', port: 6379 });
const subClient = pubClient.duplicate();

io.adapter(createAdapter(pubClient, subClient));
```

### Load Balancing

```nginx
upstream swaply_backend {
    server 192.168.1.10:3000;
    server 192.168.1.11:3000;
    server 192.168.1.12:3000;
}

server {
    location /socket.io {
        proxy_pass http://swaply_backend;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
}
```

## 🧪 Tests de Charge

### Artillery

```yaml
# load_test.yml
config:
  target: 'https://yourdomain.com'
  phases:
    - duration: 60
      arrivalRate: 10
      name: 'Ramping up'

scenarios:
  - name: 'Test d\'appel vidéo'
    flow:
      - get:
          url: '/controller/VideoCallController.php?action=getActive&id_conversation=1'
      - think: 5
      - post:
          url: '/controller/VideoCallController.php?action=initiate'
          json:
            id_conversation: 1
            type_appel: '1to1'
```

Lancer:
```bash
artillery run load_test.yml
```

## 📈 Gestion des Erreurs

### Retry Logic

```javascript
async function initiateCallWithRetry(conversationId, maxRetries = 3) {
    for (let i = 0; i < maxRetries; i++) {
        try {
            return await this.videoManager.initiateCall(conversationId);
        } catch (error) {
            if (i < maxRetries - 1) {
                await sleep(Math.pow(2, i) * 1000); // Backoff exponentiel
            } else {
                throw error;
            }
        }
    }
}
```

### Error Tracking

```javascript
const Sentry = require('@sentry/node');

Sentry.init({ dsn: 'YOUR_SENTRY_DSN' });

try {
    // Code
} catch (error) {
    Sentry.captureException(error);
}
```

## 🔄 Continuité de Service

### Backup BDD

```bash
#!/bin/bash
# backup.sh
BACKUP_DIR="/backups/swaply"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

mysqldump -u root -p swaply > "$BACKUP_DIR/swaply_$TIMESTAMP.sql"

# Nettoyer les vieux backups
find $BACKUP_DIR -name "swaply_*.sql" -mtime +30 -delete
```

### Disaster Recovery

```sql
-- Restaurer depuis backup
mysql -u root -p swaply < /backups/swaply/swaply_20260504_120000.sql

-- Vérifier l'intégrité
CHECK TABLE video_calls;
CHECK TABLE video_call_participants;
```

## 📱 CDN Configuration

```php
// Utiliser CDN pour assets statiques
define('CDN_URL', 'https://cdn.yourdomain.com');

// Dans les templates
<link rel="stylesheet" href="<?php echo CDN_URL; ?>/asset/css/videocall.css">
<script src="<?php echo CDN_URL; ?>/asset/js/VideoCallManager.js"></script>
```

## 🎯 Post-Déploiement

### Tests

- [ ] Test d'appel simple 1-to-1
- [ ] Test de rejet d'appel
- [ ] Test des contrôles audio/vidéo
- [ ] Test sur mobile
- [ ] Test avec mauvaise connexion
- [ ] Test de charge
- [ ] Test de failover

### Monitoring Continu

- [ ] Vérifier les logs d'erreur
- [ ] Monitorer la latence réseau
- [ ] Vérifier l'usage CPU/Mémoire
- [ ] Analyser les performances
- [ ] Mesurer la disponibilité

## 🚨 Rollback Plan

```bash
# Si problème critique
pm2 stop swaply-video-server
git checkout <version_stable>
npm install
npm start

# Ou reverser BDD
mysql -u root -p swaply < /backups/swaply/swaply_stable.sql
```

## 📞 Support Production

Contacter:
1. Administrateur serveur
2. Devops team
3. Support technique

Informations à fournir:
- Logs du serveur
- Logs du navigateur
- Heure du problème
- Actions reproduisant le problème
- Configuration système

---

**Production checklist complète!** 🎉
