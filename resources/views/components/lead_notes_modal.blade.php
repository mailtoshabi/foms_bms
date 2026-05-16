<div class="modal fade" id="leadNotesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lead Notes - <span id="leadNotesName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Added By</th>
                                <th>Status</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody id="leadNotesTableBody">
                            <!-- Notes will be populated here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Use jQuery for event delegation to ensure it works even if content is dynamically loaded
        $(document).on('click', '.viewLeadNotes', function(e) {
            e.preventDefault();
            
            let btn = $(this);
            let leadName = btn.data('name');
            let notes = btn.data('notes');
            
            // If notes is a string, parse it. jQuery .data() usually parses it automatically though.
            if (typeof notes === 'string') {
                try {
                    notes = JSON.parse(notes);
                } catch(err) {
                    notes = [];
                }
            }
            
            if(!Array.isArray(notes)) notes = [];

            $('#leadNotesName').text(leadName);
            let tbody = $('#leadNotesTableBody');
            tbody.empty();

            if (notes.length === 0) {
                tbody.append('<tr><td colspan="4" class="text-center text-muted">No notes found for this lead.</td></tr>');
            } else {
                notes.forEach(function(note) {
                    let dateObj = new Date(note.created_at);
                    let dateStr = !isNaN(dateObj.getTime()) ? dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
                    let staffName = note.staff ? note.staff.name : '-';
                    let status = note.status ? note.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : '-';
                    
                    let badgeColor = 'bg-secondary';
                    if(status.toLowerCase().includes('pending')) badgeColor = 'bg-warning';
                    else if(status.toLowerCase().includes('interested')) badgeColor = 'bg-success';
                    else if(status.toLowerCase().includes('not')) badgeColor = 'bg-danger';
                    else if(status.toLowerCase().includes('follow')) badgeColor = 'bg-info';

                    let tr = `
                        <tr>
                            <td class="text-nowrap">${dateStr}</td>
                            <td>${staffName}</td>
                            <td><span class="badge ${badgeColor}">${status}</span></td>
                            <td style="white-space: pre-wrap;">${note.note}</td>
                        </tr>
                    `;
                    tbody.append(tr);
                });
            }

            $('#leadNotesModal').modal('show');
        });
    });
</script>
