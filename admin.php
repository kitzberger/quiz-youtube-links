<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YouTube Links Editor</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #1a1a1a;
            color: #fff;
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            margin-bottom: 20px;
        }

        h2 {
            margin: 20px 0 10px;
            color: #888;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        select, input, button {
            font-family: inherit;
            font-size: 14px;
        }

        select {
            background: #2a2a2a;
            color: #fff;
            border: 1px solid #444;
            padding: 10px 15px;
            border-radius: 4px;
            min-width: 200px;
        }

        input[type="text"], input[type="number"] {
            background: #2a2a2a;
            color: #fff;
            border: 1px solid #444;
            padding: 8px 12px;
            border-radius: 4px;
        }

        input[type="text"]:focus, input[type="number"]:focus, select:focus {
            outline: none;
            border-color: #0066cc;
        }

        button {
            background: #0066cc;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover {
            background: #0052a3;
        }

        button.secondary {
            background: #444;
        }

        button.secondary:hover {
            background: #555;
        }

        button.danger {
            background: #dc3545;
        }

        button.danger:hover {
            background: #c82333;
        }

        button.success {
            background: #28a745;
        }

        button.success:hover {
            background: #218838;
        }

        .group {
            background: #2a2a2a;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .group-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #444;
        }

        .group-header input {
            flex: 1;
            font-size: 16px;
            font-weight: bold;
        }

        .video-item {
            display: grid;
            grid-template-columns: 200px 1fr 80px 80px auto;
            gap: 10px;
            align-items: center;
            padding: 10px;
            background: #333;
            border-radius: 4px;
            margin-bottom: 8px;
        }

        .video-item .url-input-group {
            display: flex;
            gap: 5px;
        }

        .video-item .url-input-group input {
            flex: 1;
            min-width: 0;
        }

        .video-item .url-input-group button {
            padding: 6px 8px;
            font-size: 11px;
            white-space: nowrap;
        }

        .video-item.loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .video-item input[type="text"] {
            width: 100%;
        }

        .video-item input[type="number"] {
            width: 100%;
        }

        .video-item .actions {
            display: flex;
            gap: 5px;
        }

        .video-item button {
            padding: 6px 10px;
            font-size: 12px;
        }

        .add-video-btn {
            width: 100%;
            background: #333;
            border: 2px dashed #555;
            margin-top: 10px;
        }

        .add-video-btn:hover {
            background: #3a3a3a;
            border-color: #666;
        }

        .message {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
            display: none;
        }

        .message.success {
            background: #28a74533;
            border: 1px solid #28a745;
            color: #28a745;
        }

        .message.error {
            background: #dc354533;
            border: 1px solid #dc3545;
            color: #dc3545;
        }

        .message.show {
            display: block;
        }

        .new-file-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .header-btn {
            background: #444;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            transition: background 0.2s;
        }

        .header-btn:hover {
            background: #555;
        }

        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .video-item {
                grid-template-columns: 1fr;
                gap: 8px;
            }

            .video-item .actions {
                justify-content: flex-end;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-row">
            <h1>YouTube Links Editor</h1>
            <a href="index.html" class="header-btn">Player</a>
        </div>

        <div id="message" class="message"></div>

        <div class="toolbar">
            <select id="fileSelect" onchange="loadFile()">
                <option value="">Select a file...</option>
            </select>
            <button onclick="saveFile()" class="success">Save</button>
            <button onclick="deleteFile()" class="danger">Delete File</button>
        </div>

        <div class="toolbar">
            <div class="new-file-form">
                <input type="text" id="newFileName" placeholder="New file name">
                <button onclick="createNewFile()" class="secondary">Create New File</button>
            </div>
        </div>

        <div id="editor"></div>

        <button onclick="addGroup()" class="secondary" style="margin-top: 15px;" id="addGroupBtn" disabled>
            + Add Group
        </button>
    </div>

    <script>
        let currentData = {};
        let currentFile = null;

        async function loadFileList() {
            try {
                const response = await fetch('api.php?list');
                const data = await response.json();
                const select = document.getElementById('fileSelect');

                // Clear existing options except first
                select.innerHTML = '<option value="">Select a file...</option>';

                data.files.forEach(file => {
                    const option = document.createElement('option');
                    option.value = file;
                    option.textContent = file;
                    select.appendChild(option);
                });

                // Restore selection if we had one
                if (currentFile && data.files.includes(currentFile)) {
                    select.value = currentFile;
                }
            } catch (error) {
                showMessage('Failed to load file list: ' + error.message, 'error');
            }
        }

        async function loadFile() {
            const select = document.getElementById('fileSelect');
            const filename = select.value;

            if (!filename) {
                currentFile = null;
                currentData = {};
                renderEditor();
                document.getElementById('addGroupBtn').disabled = true;
                return;
            }

            try {
                const response = await fetch(`api.php?file=${encodeURIComponent(filename)}`);
                if (!response.ok) {
                    throw new Error('File not found');
                }
                const data = await response.json();
                // Ensure it's a plain object, not an array
                currentData = (Array.isArray(data) || typeof data !== 'object' || data === null) ? {} : data;
                currentFile = filename;
                renderEditor();
                document.getElementById('addGroupBtn').disabled = false;
            } catch (error) {
                showMessage('Failed to load file: ' + error.message, 'error');
            }
        }

        async function saveFile() {
            if (!currentFile) {
                showMessage('No file selected', 'error');
                return;
            }

            try {
                const response = await fetch(`api.php?file=${encodeURIComponent(currentFile)}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(currentData)
                });

                const result = await response.json();

                if (result.success) {
                    showMessage('File saved successfully!', 'success');
                } else {
                    throw new Error(result.error || 'Unknown error');
                }
            } catch (error) {
                showMessage('Failed to save file: ' + error.message, 'error');
            }
        }

        async function deleteFile() {
            if (!currentFile) {
                showMessage('No file selected', 'error');
                return;
            }

            if (!confirm(`Are you sure you want to delete "${currentFile}"?`)) {
                return;
            }

            try {
                const response = await fetch(`api.php?file=${encodeURIComponent(currentFile)}`, {
                    method: 'DELETE'
                });

                const result = await response.json();

                if (result.success) {
                    showMessage('File deleted successfully!', 'success');
                    currentFile = null;
                    currentData = {};
                    document.getElementById('fileSelect').value = '';
                    renderEditor();
                    loadFileList();
                    document.getElementById('addGroupBtn').disabled = true;
                } else {
                    throw new Error(result.error || 'Unknown error');
                }
            } catch (error) {
                showMessage('Failed to delete file: ' + error.message, 'error');
            }
        }

        async function createNewFile() {
            const input = document.getElementById('newFileName');
            const filename = input.value.trim();

            if (!filename) {
                showMessage('Please enter a file name', 'error');
                return;
            }

            try {
                const response = await fetch(`api.php?file=${encodeURIComponent(filename)}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                });

                const result = await response.json();

                if (result.success) {
                    showMessage('File created successfully!', 'success');
                    input.value = '';
                    currentFile = result.file;
                    currentData = {};
                    await loadFileList();
                    document.getElementById('fileSelect').value = currentFile;
                    renderEditor();
                    document.getElementById('addGroupBtn').disabled = false;
                } else {
                    throw new Error(result.error || 'Unknown error');
                }
            } catch (error) {
                showMessage('Failed to create file: ' + error.message, 'error');
            }
        }

        function showMessage(text, type) {
            const msg = document.getElementById('message');
            msg.textContent = text;
            msg.className = `message ${type} show`;

            setTimeout(() => {
                msg.classList.remove('show');
            }, 3000);
        }

        function renderEditor() {
            const editor = document.getElementById('editor');
            editor.innerHTML = '';

            if (!currentFile) {
                editor.innerHTML = '<p style="color: #888; text-align: center; padding: 40px;">Select or create a file to start editing</p>';
                return;
            }

            const groups = Object.keys(currentData);

            if (groups.length === 0) {
                editor.innerHTML = '<p style="color: #888; text-align: center; padding: 40px;">No groups yet. Click "Add Group" to create one.</p>';
                return;
            }

            groups.forEach(groupName => {
                const group = document.createElement('div');
                group.className = 'group';
                group.innerHTML = `
                    <div class="group-header">
                        <input type="text" value="${escapeHtml(groupName)}" onchange="renameGroup('${escapeHtml(groupName)}', this.value)">
                        <button class="danger" onclick="deleteGroup('${escapeHtml(groupName)}')">Delete Group</button>
                    </div>
                    <div class="video-list" id="group-${escapeHtml(groupName)}">
                        ${renderVideos(groupName)}
                    </div>
                    <button class="add-video-btn secondary" onclick="addVideo('${escapeHtml(groupName)}')">+ Add Video</button>
                `;
                editor.appendChild(group);
            });
        }

        function extractYouTubeId(input) {
            // Already a valid ID (11 chars, alphanumeric with - and _)
            if (/^[a-zA-Z0-9_-]{11}$/.test(input)) {
                return input;
            }

            // Try to parse as URL
            try {
                const url = new URL(input);

                // youtube.com/watch?v=ID
                if (url.hostname.includes('youtube.com')) {
                    const v = url.searchParams.get('v');
                    if (v) return v;

                    // youtube.com/embed/ID or youtube.com/v/ID
                    const match = url.pathname.match(/^\/(embed|v)\/([a-zA-Z0-9_-]{11})/);
                    if (match) return match[2];

                    // youtube.com/shorts/ID
                    const shortsMatch = url.pathname.match(/^\/shorts\/([a-zA-Z0-9_-]{11})/);
                    if (shortsMatch) return shortsMatch[1];
                }

                // youtu.be/ID
                if (url.hostname === 'youtu.be') {
                    const id = url.pathname.slice(1).split('?')[0];
                    if (/^[a-zA-Z0-9_-]{11}$/.test(id)) return id;
                }
            } catch (e) {
                // Not a valid URL
            }

            return null;
        }

        async function fetchVideoTitle(videoId) {
            try {
                const response = await fetch(`api.php?youtube_info=${encodeURIComponent(videoId)}`);
                if (!response.ok) return null;
                const data = await response.json();
                return data.title || null;
            } catch (e) {
                return null;
            }
        }

        async function handleUrlInput(groupName, index, inputValue) {
            const videoId = extractYouTubeId(inputValue);

            if (!videoId) {
                showMessage('Could not extract YouTube ID from input', 'error');
                return;
            }

            // Update the ID
            updateVideo(groupName, index, 'id', videoId);

            // Try to fetch the title
            const videoItem = document.querySelector(`[data-group="${groupName}"][data-index="${index}"]`);
            if (videoItem) videoItem.classList.add('loading');

            const title = await fetchVideoTitle(videoId);

            if (videoItem) videoItem.classList.remove('loading');

            if (title) {
                updateVideo(groupName, index, 'name', title);
                showMessage(`Fetched: ${title}`, 'success');
            }

            renderEditor();
        }

        function renderVideos(groupName) {
            const videos = currentData[groupName] || [];

            if (videos.length === 0) {
                return '<p style="color: #666; text-align: center; padding: 10px;">No videos in this group</p>';
            }

            return videos.map((video, index) => `
                <div class="video-item" data-group="${escapeHtml(groupName)}" data-index="${index}">
                    <div class="url-input-group">
                        <input type="text" value="${escapeHtml(video.id || '')}" placeholder="YouTube URL or ID" id="url-${escapeHtml(groupName)}-${index}"
                               onchange="updateVideoId('${escapeHtml(groupName)}', ${index}, this.value)">
                        <button class="secondary" onclick="handleUrlInput('${escapeHtml(groupName)}', ${index}, document.getElementById('url-${escapeHtml(groupName)}-${index}').value)">Fetch</button>
                    </div>
                    <input type="text" value="${escapeHtml(video.name || '')}" placeholder="Video name"
                           onchange="updateVideo('${escapeHtml(groupName)}', ${index}, 'name', this.value)">
                    <input type="number" value="${video.startTime ?? 0}" placeholder="Start"
                           onchange="updateVideo('${escapeHtml(groupName)}', ${index}, 'startTime', parseInt(this.value) || 0)">
                    <input type="number" value="${video.endTime ?? ''}" placeholder="End"
                           onchange="updateVideo('${escapeHtml(groupName)}', ${index}, 'endTime', this.value ? parseInt(this.value) : null)">
                    <div class="actions">
                        <button class="secondary" onclick="moveVideo('${escapeHtml(groupName)}', ${index}, -1)" ${index === 0 ? 'disabled' : ''}>↑</button>
                        <button class="secondary" onclick="moveVideo('${escapeHtml(groupName)}', ${index}, 1)" ${index === videos.length - 1 ? 'disabled' : ''}>↓</button>
                        <button class="danger" onclick="deleteVideo('${escapeHtml(groupName)}', ${index})">×</button>
                    </div>
                </div>
            `).join('');
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        function addGroup() {
            const name = prompt('Enter group name:');
            if (name && name.trim()) {
                const safeName = name.trim();
                if (currentData[safeName]) {
                    showMessage('Group already exists', 'error');
                    return;
                }
                currentData[safeName] = [];
                renderEditor();
            }
        }

        function renameGroup(oldName, newName) {
            newName = newName.trim();
            if (!newName) {
                renderEditor();
                return;
            }
            if (newName === oldName) return;
            if (currentData[newName]) {
                showMessage('Group already exists', 'error');
                renderEditor();
                return;
            }

            // Preserve order by rebuilding object
            const newData = {};
            Object.keys(currentData).forEach(key => {
                if (key === oldName) {
                    newData[newName] = currentData[oldName];
                } else {
                    newData[key] = currentData[key];
                }
            });
            currentData = newData;
            renderEditor();
        }

        function deleteGroup(name) {
            if (confirm(`Delete group "${name}" and all its videos?`)) {
                delete currentData[name];
                renderEditor();
            }
        }

        function addVideo(groupName) {
            currentData[groupName].push({
                id: '',
                name: 'New Video',
                startTime: 0,
                endTime: null
            });
            renderEditor();
        }

        function updateVideo(groupName, index, field, value) {
            if (currentData[groupName] && currentData[groupName][index]) {
                currentData[groupName][index][field] = value;
            }
        }

        function updateVideoId(groupName, index, input) {
            const videoId = extractYouTubeId(input);
            if (videoId) {
                updateVideo(groupName, index, 'id', videoId);
            } else {
                // Store raw input if we can't extract an ID
                updateVideo(groupName, index, 'id', input);
            }
        }

        function deleteVideo(groupName, index) {
            if (confirm('Delete this video?')) {
                currentData[groupName].splice(index, 1);
                renderEditor();
            }
        }

        function moveVideo(groupName, index, direction) {
            const videos = currentData[groupName];
            const newIndex = index + direction;

            if (newIndex < 0 || newIndex >= videos.length) return;

            const temp = videos[index];
            videos[index] = videos[newIndex];
            videos[newIndex] = temp;

            renderEditor();
        }

        // Initialize
        loadFileList();
        renderEditor();
    </script>
</body>
</html>
