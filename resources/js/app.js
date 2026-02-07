import './bootstrap';
import * as bootstrap from 'bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.addEventListener('DOMContentLoaded', function () {
    // Initialize Modal
    const alertModal = new bootstrap.Modal(document.getElementById('alertDetailsModal'));
    const modalContent = document.getElementById('alertDetailsContent');

    // Handle row click to open modal
    const alertRows = document.querySelectorAll('.alert-row');

    alertRows.forEach(row => {
        row.addEventListener('click', function (e) {
            // Prevent modal if clicking buttons or links directly
            if (e.target.closest('button') || e.target.closest('a')) {
                return;
            }

            const alertId = this.dataset.alertId;

            // Show modal with loading state
            modalContent.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';
            alertModal.show();

            // Fetch details
            const baseUrl = document.querySelector('meta[name="app-url"]').content;
            const url = baseUrl.replace(/\/$/, '');
            const fetchUrl = `${url}/alerts/${alertId}`;

            fetch(fetchUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    modalContent.innerHTML = data.html;
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    modalContent.innerHTML = `<div class="alert alert-danger">Failed to load details. check console for URL: ${fetchUrl}</div>`;
                });
        });
    });

    // Handle "Take" button (Delegated for both list and modal)
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-take-alert')) {
            e.preventDefault();
            const btn = e.target;
            const alertId = btn.dataset.alertId;
            const baseUrl = document.querySelector('meta[name="app-url"]').content;
            const url = baseUrl.replace(/\/$/, '');
            const postUrl = `${url}/alerts/${alertId}/take`;

            // Disable button
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Taking...';

            fetch(postUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Reload to update UI state across the board
                        location.reload();
                    } else {
                        alert(data.error);
                        btn.disabled = false;
                        btn.innerText = 'Take';
                    }
                })
                .catch(error => {
                    console.error('Take Action Error:', error);
                    alert(`Failed to take alert. Check console for URL: ${postUrl}`);
                    btn.disabled = false;
                    btn.innerText = 'Take';
                });
        }

        // Handle "Release" button
        if (e.target.classList.contains('btn-release-alert')) {
            e.preventDefault();
            const btn = e.target;
            const alertId = btn.dataset.alertId;
            const baseUrl = document.querySelector('meta[name="app-url"]').content;
            const url = baseUrl.replace(/\/$/, '');
            const postUrl = `${url}/alerts/${alertId}/release`;

            // Disable button
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Releasing...';

            fetch(postUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error);
                        btn.disabled = false;
                        btn.innerText = 'Release Alert';
                    }
                })
                .catch(error => {
                    console.error('Release Action Error:', error);
                    alert('Failed to release alert.');
                    btn.disabled = false;
                    btn.innerHTML = 'Release Alert';
                });
        }

        // Handle "Resolve Submit" button
        if (e.target.classList.contains('btn-resolve-submit')) {
            e.preventDefault();
            const btn = e.target;
            const alertId = btn.dataset.alertId;
            const notesInput = document.getElementById(`resolution-notes-${alertId}`);
            const ticketInput = document.getElementById(`ticket-number-${alertId}`);

            if (!notesInput.value.trim()) {
                alert('Resolution notes are required.');
                return;
            }

            const baseUrl = document.querySelector('meta[name="app-url"]').content;
            const url = baseUrl.replace(/\/$/, '');
            const postUrl = `${url}/alerts/${alertId}/resolve`;

            // Disable button
            btn.disabled = true;
            btn.innerHTML = 'Saving...';

            fetch(postUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    resolution_notes: notesInput.value,
                    ticket_number: ticketInput.value
                })
            })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error || 'Failed to resolve alert.');
                        btn.disabled = false;
                        btn.innerHTML = 'Submit';
                    }
                })
                .catch(error => {
                    console.error('Resolve Error:', error);
                    alert('Failed to resolve alert.');
                    btn.disabled = false;
                    btn.innerHTML = 'Submit';
                });
        }

        // Handle "Close" button
        if (e.target.classList.contains('btn-close-alert')) {
            if (!confirm('Are you sure you want to close this alert? This is a final action.')) return;

            e.preventDefault();
            const btn = e.target;
            const alertId = btn.dataset.alertId;
            const baseUrl = document.querySelector('meta[name="app-url"]').content;
            const url = baseUrl.replace(/\/$/, '');
            const postUrl = `${url}/alerts/${alertId}/close`;

            btn.disabled = true;
            btn.innerHTML = 'Closing...';

            fetch(postUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error || 'Failed to close alert.');
                        btn.disabled = false;
                        btn.innerHTML = 'Close Alert';
                    }
                })
                .catch(error => {
                    console.error('Close Error:', error);
                    alert('Failed to close alert.');
                    btn.disabled = false;
                    btn.innerHTML = 'Close Alert';
                });
        }

        // Handle "Reopen" button
        if (e.target.classList.contains('btn-reopen-alert')) {
            if (!confirm('Are you sure you want to reopen this alert?')) return;

            e.preventDefault();
            const btn = e.target;
            const alertId = btn.dataset.alertId;
            const baseUrl = document.querySelector('meta[name="app-url"]').content;
            const url = baseUrl.replace(/\/$/, '');
            const postUrl = `${url}/alerts/${alertId}/reopen`;

            btn.disabled = true;
            btn.innerHTML = 'Reopening...';

            fetch(postUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error || 'Failed to reopen alert.');
                        btn.disabled = false;
                        btn.innerHTML = 'Reopen Alert';
                    }
                })
                .catch(error => {
                    console.error('Reopen Error:', error);
                    alert('Failed to reopen alert.');
                    btn.disabled = false;
                    btn.innerHTML = 'Reopen Alert';
                });
        }
    });

    // Bulk Actions Logic
    const selectAllCheckbox = document.getElementById('select-all-alerts');
    const alertCheckboxes = document.querySelectorAll('.alert-checkbox');
    const bulkActionBar = document.getElementById('bulk-action-bar');
    const selectedCountSpan = document.getElementById('selected-count');
    const bulkResolveCountSpan = document.getElementById('bulk-resolve-count');
    const btnBulkResolveSubmit = document.getElementById('btn-bulk-resolve-submit');

    function updateBulkActionBar() {
        const selected = document.querySelectorAll('.alert-checkbox:checked');
        const count = selected.length;

        selectedCountSpan.innerText = count;
        if (bulkResolveCountSpan) bulkResolveCountSpan.innerText = count;

        if (count > 0) {
            bulkActionBar.classList.remove('d-none');
        } else {
            bulkActionBar.classList.add('d-none');
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            alertCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateBulkActionBar();
        });
    }

    alertCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActionBar);
        cb.addEventListener('click', function (e) {
            e.stopPropagation(); // Prevent row click
        });
    });

    if (btnBulkResolveSubmit) {
        btnBulkResolveSubmit.addEventListener('click', function () {
            const selectedIds = Array.from(document.querySelectorAll('.alert-checkbox:checked')).map(cb => cb.value);
            const notes = document.getElementById('bulk-resolution-notes').value;
            const ticket = document.getElementById('bulk-ticket-number').value;

            if (selectedIds.length === 0) return;
            if (!notes.trim()) {
                alert('Resolution notes are required.');
                return;
            }

            const btn = this;
            btn.disabled = true;
            btn.innerText = 'Processing...';

            const baseUrl = document.querySelector('meta[name="app-url"]').content;
            const url = baseUrl.replace(/\/$/, '');
            const postUrl = `${url}/alerts/bulk-resolve`;

            fetch(postUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    alert_ids: selectedIds,
                    resolution_notes: notes,
                    ticket_number: ticket
                })
            })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error || 'Failed to resolve alerts.');
                        btn.disabled = false;
                        btn.innerText = 'Resolve & Close';
                    }
                })
                .catch(error => {
                    console.error('Bulk Resolve Error:', error);
                    alert('Failed to resolve alerts.');
                    btn.disabled = false;
                    btn.innerText = 'Resolve & Close';
                });
        });
    }
    // Live Polling Logic
    const pollToggle = document.getElementById('livePollingToggle');
    const pollIntervalSelect = document.getElementById('pollingInterval');
    const pollIndicator = document.getElementById('pollingCountdown');
    const secondsSpan = document.getElementById('syncSeconds');
    let pollInterval = null;
    let secondsLeft = 10;

    function getSelectedInterval() {
        return parseInt(pollIntervalSelect ? pollIntervalSelect.value : 10);
    }

    function startPolling() {
        const isForced = !!document.getElementById('forceLivePolling');
        if (!isForced && (!pollToggle || !pollToggle.checked)) return;

        // Reset countdown to current selected interval
        secondsLeft = getSelectedInterval();
        if (secondsSpan) secondsSpan.innerText = secondsLeft;

        if (pollIndicator) pollIndicator.style.display = 'block';

        // Clear any existing interval just in case
        if (pollInterval) clearInterval(pollInterval);

        pollInterval = setInterval(() => {
            secondsLeft--;
            if (secondsSpan) secondsSpan.innerText = secondsLeft;

            if (secondsLeft <= 0) {
                clearInterval(pollInterval);
                performAutoSync();
            }
        }, 1000);
    }

    function stopPolling() {
        if (pollInterval) clearInterval(pollInterval);
        if (pollIndicator) pollIndicator.style.display = 'none';
        secondsLeft = getSelectedInterval();
        if (secondsSpan) secondsSpan.innerText = secondsLeft;
    }

    function performAutoSync() {
        const baseUrl = document.querySelector('meta[name="app-url"]').content;
        const url = baseUrl.replace(/\/$/, '');
        const syncUrl = `${url}/alerts/sync`;

        if (secondsSpan) secondsSpan.innerText = '...';

        fetch(syncUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    console.error('Auto-sync failed:', data.message);
                    startPolling();
                }
            })
            .catch(error => {
                console.error('Polling Error:', error);
                startPolling();
            });
    }

    const forcePollElem = document.getElementById('forceLivePolling');
    if (pollToggle || forcePollElem) {
        const isForced = !!forcePollElem;

        // Load interval preference
        const savedInterval = localStorage.getItem('livePollingInterval');
        if (savedInterval && pollIntervalSelect) {
            pollIntervalSelect.value = savedInterval;
        }

        // Initialize secondsLeft
        secondsLeft = getSelectedInterval();
        if (secondsSpan) secondsSpan.innerText = secondsLeft;

        // Load toggle state or force enabled
        const isEnabled = isForced ? true : (localStorage.getItem('livePollingEnabled') === 'true');

        if (pollToggle) pollToggle.checked = isEnabled;
        if (isEnabled) startPolling();

        if (pollToggle) {
            pollToggle.addEventListener('change', function () {
                localStorage.setItem('livePollingEnabled', this.checked);
                if (this.checked) {
                    startPolling();
                } else {
                    stopPolling();
                }
            });
        }

        if (pollIntervalSelect) {
            pollIntervalSelect.addEventListener('change', function () {
                localStorage.setItem('livePollingInterval', this.value);
                // If polling is active (either enabled or forced), restart
                if ((pollToggle && pollToggle.checked) || isForced) {
                    startPolling();
                } else {
                    secondsLeft = getSelectedInterval();
                    if (secondsSpan) secondsSpan.innerText = secondsLeft;
                }
            });
        }
    }
});
