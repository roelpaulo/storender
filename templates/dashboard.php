<div class="grid lg:grid-cols-[300px_1fr] gap-6" x-data="dashboard()">
  <!-- Sidebar -->
  <aside class="flex flex-col gap-6">
    <div class="card bg-base-100 shadow border border-base-200">
      <div class="card-body p-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-bold uppercase text-xs tracking-widest text-base-content/50">Projects</h3>
          <button class="btn btn-circle btn-xs btn-primary text-white" @click="showModal('new_project')">+</button>
        </div>

        <div class="flex flex-col gap-1 overflow-y-auto max-h-[60vh]">
          <template x-for="p in projects" :key="p.id">
            <button @click="selectProject(p)" class="flex flex-col items-start p-3 rounded-xl transition-all text-left"
              :class="currentProject?.id === p.id ? 'bg-primary text-primary-content shadow-lg' : 'hover:bg-base-200 bg-base-200/30'">
              <span class="font-semibold" x-text="p.name"></span>
              <span class="text-[10px] uppercase tracking-wider opacity-60" x-text="p.slug"></span>
            </button>
          </template>
          <div x-show="projects.length === 0" class="text-center py-8 text-base-content/40 text-sm italic">
            No projects yet
          </div>
        </div>
      </div>
    </div>

    <div class="card bg-base-100 shadow border border-base-200" x-show="currentProject">
      <div class="card-body p-4">
        <h3 class="font-bold uppercase text-xs tracking-widest text-base-content/50 mb-4">Quick Stats</h3>
        <div class="grid grid-cols-1 gap-2">
          <div class="stat p-0">
            <div class="stat-title text-[10px]">Total Files</div>
            <div class="stat-value text-lg" x-text="files.length">0</div>
          </div>
        </div>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
  <section>
    <template x-if="!currentProject">
      <div
        class="flex flex-col items-center justify-center h-full min-h-[50vh] text-center p-12 bg-base-100/30 rounded-3xl border-2 border-dashed border-base-content/10">
        <div class="text-6xl mb-4 opacity-20 italic font-black">STORENDER</div>
        <h2 class="text-xl font-bold opacity-60">Select a project to manage assets</h2>
        <p class="text-base-content/50 mt-2">All files are scoped and isolated by project</p>
      </div>
    </template>

    <template x-if="currentProject">
      <div class="flex flex-col gap-6" x-ref="detail">
        <div class="card bg-base-100 shadow-xl border border-base-200">
          <div class="card-body">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
              <div>
                <div class="badge badge-outline badge-sm mb-1 uppercase tracking-tighter"
                  x-text="'PROJECT ID: ' + currentProject.id"></div>
                <h1 class="text-3xl font-black italic tracking-tighter" x-text="currentProject.name"></h1>
              </div>
              <div class="flex gap-2">
                <button class="btn btn-primary btn-sm" @click="showModal('upload')">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                  </svg>
                  Upload
                </button>
                <button class="btn btn-ghost btn-outline btn-sm" @click="showSettings()">Settings</button>
                <button class="btn btn-ghost btn-outline btn-sm" @click="showModal('api_keys')">API Keys</button>
                <button class="btn btn-error btn-outline btn-sm text-error hover:text-white" @click="deleteProject()">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>

            <div class="divider my-0"></div>

            <div class="overflow-x-auto">
              <table class="table table-zebra font-sans">
                <thead>
                  <tr class="text-base-content/50 uppercase text-[10px] tracking-widest">
                    <td>Asset</td>
                    <td>Details</td>
                    <td>Size</td>
                    <td>Joined</td>
                    <td>Visibility</td>
                    <td class="text-right">Actions</td>
                  </tr>
                </thead>
                <tbody>
                  <template x-for="f in files" :key="f.id">
                    <tr class="hover">
                      <td>
                        <div class="flex items-center gap-3">
                          <div
                            class="mask mask-squircle w-10 h-10 bg-base-200 flex items-center justify-center overflow-hidden">
                            <template x-if="f.mime_type.startsWith('image/')">
                              <img :src="'/api/files/' + f.id" class="w-full h-full object-cover" />
                            </template>
                            <template x-if="!f.mime_type.startsWith('image/')">
                              <span class="text-[8px] font-bold"
                                x-text="f.mime_type.split('/')[1].toUpperCase()"></span>
                            </template>
                          </div>
                          <div>
                            <div class="font-bold text-sm truncate max-w-[200px]" x-text="f.original_name"></div>
                            <div class="text-[10px] opacity-40 font-mono" x-text="f.id"></div>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="badge badge-ghost badge-xs" x-text="f.mime_type"></div>
                      </td>
                      <td class="text-xs opacity-60" x-text="formatSize(f.size)"></td>
                      <td class="text-[10px] opacity-60" x-text="new Date(f.created_at).toLocaleDateString()"></td>
                      <td>
                        <button @click="toggleVisibility(f)" class="btn btn-ghost btn-xs gap-1 normal-case font-medium"
                          :class="f.is_public ? 'text-success' : 'text-warning'">
                          <template x-if="f.is_public">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                              stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                            </svg>
                          </template>
                          <template x-if="!f.is_public">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                              stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                          </template>
                          <span x-text="f.is_public ? 'Public' : 'Private'"></span>
                        </button>
                      </td>
                      <td class="text-right">
                        <div class="join">
                          <button class="btn btn-xs btn-ghost join-item" title="Copy URL" @click="copyUrl(f.id)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                              stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                            </svg>
                          </button>
                          <a :href="'/api/files/' + f.id" target="_blank" class="btn btn-xs btn-ghost join-item"
                            title="Download">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                              stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                          </a>
                          <button class="btn btn-xs btn-ghost join-item text-error" title="Delete"
                            @click="deleteFile(f.id)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                              stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
              <div x-show="files.length === 0" class="text-center py-24 italic opacity-40">
                No files uploaded to this project yet.
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>
  </section>

  <!-- Modals -->
  <dialog id="modal_new_project" class="modal">
    <div class="modal-box bg-base-100 border border-base-200">
      <h3 class="font-bold text-lg">Create New Project</h3>
      <p class="py-4 text-xs opacity-50">Projects act as logical boundaries for your assets.</p>
      <div class="form-control">
        <label class="label"><span class="label-text">Project Name</span></label>
        <input type="text" x-model="modalData.projectName" placeholder="e.g. My Website API"
          class="input input-bordered w-full" />
      </div>
      <div class="modal-action">
        <button class="btn btn-primary" @click="createProject()" :disabled="!modalData.projectName">Create</button>
        <button class="btn" onclick="modal_new_project.close()">Cancel</button>
      </div>
    </div>
  </dialog>

  <dialog id="modal_api_keys" class="modal">
    <div class="modal-box w-11/12 max-w-2xl bg-base-100 border border-base-200">
      <h3 class="font-bold text-lg mb-4">API Keys</h3>
      <div class="overflow-x-auto">
        <table class="table table-xs">
          <thead>
            <tr>
              <th>Label</th>
              <th>Last Used</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <template x-for="k in apiKeys" :key="k.id">
              <tr>
                <td class="font-bold" x-text="k.label"></td>
                <td class="opacity-60" x-text="k.last_used_at || 'Never'"></td>
                <td class="text-right">
                  <button class="btn btn-xs btn-error btn-outline" @click="deleteKey(k.id)">Revoke</button>
                </td>
              </tr>
            </template>
            <tr x-show="apiKeys.length === 0">
              <td colspan="3" class="text-center py-4 italic opacity-40">No keys generated</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="divider my-4"></div>

      <div class="bg-base-200 p-4 rounded-xl">
        <h4 class="font-bold text-sm mb-2">Generate New Key</h4>
        <div class="flex gap-2">
          <input type="text" x-model="modalData.keyLabel" placeholder="Key Label (e.g. Production)"
            class="input input-sm input-bordered w-full" />
          <button class="btn btn-sm btn-primary" @click="createKey()">Generate</button>
        </div>
      </div>

      <div class="modal-action">
        <button class="btn" onclick="modal_api_keys.close()">Close</button>
      </div>
    </div>
  </dialog>

  <dialog id="modal_upload" class="modal">
    <div class="modal-box bg-base-100 border border-base-200">
      <h3 class="font-bold text-lg">Upload Asset</h3>
      <p class="py-2 text-xs opacity-50" x-text="'Uploading to ' + currentProject?.name"></p>

      <div class="form-control w-full mt-4">
        <input type="file" id="asset_upload_input" class="file-input file-input-bordered file-input-primary w-full" />
        <label class="label"><span class="label-text-alt opacity-50">Maximum file size: 128MB</span></label>
      </div>

      <div x-show="uploading" class="mt-4">
        <div class="flex justify-between text-xs mb-1">
          <span x-text="uploadProgress + '%'"></span>
          <span x-text="uploadStatus">Preparing...</span>
        </div>
        <progress class="progress progress-primary w-full" :value="uploadProgress" max="100"></progress>
      </div>

      <div class="modal-action">
        <button class="btn btn-primary" @click="performUpload()" :disabled="uploading">Start Upload</button>
        <button class="btn" onclick="modal_upload.close()" :disabled="uploading">Cancel</button>
      </div>
    </div>
  </dialog>

  <dialog id="modal_project_settings" class="modal">
    <div class="modal-box bg-base-100 border border-base-200">
      <h3 class="font-bold text-lg">Project Settings</h3>
      <p class="py-2 text-xs opacity-50">Manage security and access control.</p>

      <div class="form-control w-full mt-4">
        <label class="label">
          <span class="label-text font-bold">Allowed Domains (Referer Security)</span>
        </label>
        <div class="text-[10px] text-base-content/60 mb-2">
          Comma-separated list of domains allowed to display Public assets (e.g.
          <code>example.com, myportfolio.dev</code>).
          Leave empty to allow all domains.
        </div>
        <textarea x-model="modalData.allowedDomains" class="textarea textarea-bordered h-24 font-mono text-xs"
          placeholder="example.com, portfolio.dev"></textarea>
      </div>

      <div class="modal-action">
        <button class="btn btn-primary" @click="updateProjectSettings()">Save Changes</button>
        <button class="btn" onclick="modal_project_settings.close()">Cancel</button>
      </div>
    </div>
  </dialog>

  <!-- UI Overlay for API Key display -->
  <dialog id="modal_key_display" class="modal">
    <div class="modal-box bg-primary text-primary-content">
      <h3 class="font-bold text-lg">New API Key Generated!</h3>
      <p class="py-4 text-sm font-medium">IMPORTANT: Copy this key now. It will NOT be shown again for security reasons.
      </p>
      <div class="bg-black/20 p-4 rounded-lg font-mono text-sm break-all select-all flex justify-between items-center">
        <span x-text="modalData.generatedKey"></span>
      </div>
      <div class="modal-action">
        <button class="btn bg-white text-primary border-none hover:bg-white/90"
          onclick="document.getElementById('modal_key_display').close()">I've
          Saved It</button>
      </div>
    </div>
  </dialog>

  <dialog id="modal_confirm" class="modal">
    <div class="modal-box border border-base-200">
      <h3 class="font-bold text-lg" :class="confirmData.type === 'error' ? 'text-error' : ''"
        x-text="confirmData.title"></h3>
      <p class="py-4 text-sm" x-text="confirmData.message"></p>
      <div x-show="confirmData.description" class="p-3 bg-base-200 rounded-lg text-xs opacity-70 mb-4"
        x-text="confirmData.description"></div>
      <div class="modal-action">
        <button class="btn" :class="confirmData.type === 'error' ? 'btn-error' : 'btn-primary'"
          @click="document.getElementById('modal_confirm').close(); confirmResolve(true)"
          x-text="confirmData.confirmText"></button>
        <button class="btn"
          @click="document.getElementById('modal_confirm').close(); confirmResolve(false)">Cancel</button>
      </div>
    </div>
  </dialog>
</div>

<script>
  function dashboard() {
    return {
      projects: [],
      currentProject: null,
      files: [],
      apiKeys: [],
      uploading: false,
      uploadProgress: 0,
      uploadStatus: '',
      modalData: {
        projectName: '',
        keyLabel: '',
        generatedKey: '',
        allowedDomains: ''
      },
      confirmData: {
        title: '',
        message: '',
        description: '',
        type: 'error',
        confirmText: 'Confirm'
      },
      confirmResolve: null,

      askConfirmation(title, message, description = '', type = 'error', confirmText = 'Confirm') {
        this.confirmData = { title, message, description, type, confirmText };
        document.getElementById('modal_confirm').showModal();
        return new Promise((resolve) => {
          this.confirmResolve = resolve;
        });
      },

      async init() {
        await this.loadProjects();
      },

      async loadProjects() {
        const res = await fetch('/api/projects', { credentials: 'same-origin' });
        if (res.ok) this.projects = await res.json();
      },

      async selectProject(p) {
        this.currentProject = p;
        await Promise.all([this.loadFiles(), this.loadKeys()]);
      },

      async loadFiles() {
        if (!this.currentProject) return;
        const res = await fetch(`/api/projects/${this.currentProject.id}/files`, { credentials: 'same-origin' });
        if (res.ok) this.files = await res.json();
      },

      async loadKeys() {
        if (!this.currentProject) return;
        const res = await fetch(`/api/projects/${this.currentProject.id}/keys`, { credentials: 'same-origin' });
        if (res.ok) this.apiKeys = await res.json();
      },

      showModal(id) {
        document.getElementById(`modal_${id}`).showModal();
      },

      showSettings() {
        this.modalData.allowedDomains = this.currentProject.allowed_domains || '';
        this.showModal('project_settings');
      },

      async updateProjectSettings() {
        const res = await fetch(`/api/projects/${this.currentProject.id}`, {
          method: 'PATCH',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ allowed_domains: this.modalData.allowedDomains })
        });

        if (res.ok) {
          this.currentProject.allowed_domains = this.modalData.allowedDomains;
          // Update in local list too
          const idx = this.projects.findIndex(p => p.id === this.currentProject.id);
          if (idx !== -1) this.projects[idx].allowed_domains = this.modalData.allowedDomains;

          document.getElementById('modal_project_settings').close();
          window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Settings saved', type: 'success' } }));
        } else {
          window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Failed to update settings', type: 'error' } }));
        }
      },

      async createProject() {
        const res = await fetch('/api/projects', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ name: this.modalData.projectName })
        });
        if (res.ok) {
          this.modalData.projectName = '';
          modal_new_project.close();
          await this.loadProjects();
          window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Project created', type: 'success' } }));
        }
      },

      async createKey() {
        const res = await fetch(`/api/projects/${this.currentProject.id}/keys`, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ label: this.modalData.keyLabel })
        });
        if (res.ok) {
          const data = await res.json();
          this.modalData.keyLabel = '';
          this.modalData.generatedKey = data.key;

          document.getElementById('modal_api_keys').close();
          document.getElementById('modal_key_display').showModal();

          await this.loadKeys();
          window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'API Key generated!', type: 'success' } }));
        }
      },

      async deleteKey(id) {
        if (!await this.askConfirmation(
          'Revoke API Key?',
          'This will immediately stop all applications using this key.',
          'Action cannot be undone.',
          'error',
          'Revoke Key'
        )) return;

        const res = await fetch(`/api/keys/${id}`, { method: 'DELETE', credentials: 'same-origin' });
        if (res.ok) {
          await this.loadKeys();
          window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Key revoked', type: 'info' } }));
        }
      },

      async performUpload() {
        const input = document.getElementById('asset_upload_input');
        if (!input.files[0]) return;

        const file = input.files[0];
        this.uploading = true;
        this.uploadProgress = 0;
        this.uploadStatus = 'Streaming to server...';

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/files');
        xhr.setRequestHeader('X-File-Name', file.name);
        xhr.setRequestHeader('Content-Type', file.type);
        xhr.setRequestHeader('X-Project-ID', this.currentProject.id);
        // In a real app we'd need to handle auth here too, 
        // but our backend controller will check session if session_start() is called.

        xhr.upload.onprogress = (e) => {
          if (e.lengthComputable) {
            this.uploadProgress = Math.round((e.loaded / e.total) * 100);
          }
        };

        xhr.onload = async () => {
          this.uploading = false;
          if (xhr.status === 201) {
            modal_upload.close();
            await this.loadFiles();
            window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Asset uploaded successfully', type: 'success' } }));
          } else {
            const data = JSON.parse(xhr.responseText);
            window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.error || 'Upload failed', type: 'error' } }));
          }
        };

        xhr.onerror = () => {
          this.uploading = false;
          window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Network error during upload', type: 'error' } }));
        };

        xhr.send(file);
      },

      async deleteFile(id) {
        if (!await this.askConfirmation(
          'Delete Asset?',
          'Permanently delete this file from storage?',
          '',
          'error',
          'Delete File'
        )) return;

        const res = await fetch(`/api/files/${id}`, {
          method: 'DELETE',
          credentials: 'same-origin',
          headers: { 'X-Project-ID': this.currentProject.id }
        });
        if (res.ok) {
          await this.loadFiles();
          window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Asset deleted', type: 'info' } }));
        } else {
          const data = await res.json();
          window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.error, type: 'error' } }));
        }
      },

      async toggleVisibility(file) {
        const newStatus = file.is_public ? 0 : 1;
        const res = await fetch(`/api/files/${file.id}`, {
          method: 'PATCH',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ is_public: newStatus })
        });
        if (res.ok) {
          file.is_public = newStatus;
          window.dispatchEvent(new CustomEvent('notify', {
            detail: {
              message: `File is now ${newStatus ? 'Public' : 'Private'}`,
              type: 'info'
            }
          }));
        }
      },

      async deleteProject() {
        if (!await this.askConfirmation(
          'Delete Project?',
          'You are about to delete this project and ALL ' + this.files.length + ' associated files.',
          'DANGER: This action is permanent and cannot be undone.',
          'error',
          'Delete Project'
        )) return;

        const res = await fetch(`/api/projects/${this.currentProject.id}`, { method: 'DELETE', credentials: 'same-origin' });
        if (res.ok) {
          this.projects = this.projects.filter(p => p.id !== this.currentProject.id);
          this.currentProject = null;
          this.files = [];
          window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Project deleted successfully', type: 'info' } }));
        } else {
          const data = await res.json();
          window.dispatchEvent(new CustomEvent('notify', { detail: { message: data.error || 'Failed to delete project', type: 'error' } }));
        }
      },

      copyUrl(id) {
        const url = `${window.location.origin}/api/files/${id}`;
        navigator.clipboard.writeText(url);
        window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'URL copied to clipboard', type: 'info' } }));
      },

      formatSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
      }
    }
  }
</script>

<style>
  .table tr td {
    vertical-align: middle;
  }

  .stat-value {
    font-family: 'Inter', sans-serif;
    font-weight: 800;
  }
</style>