<?php
require_once __DIR__ . '/../../../controllers/LanguageController.php';
$lang = LanguageController::getInstance();
require_once __DIR__ . '/../../../views/header.php';
?>
<main class="container">
    <article>
        <h1><?php echo htmlspecialchars($lang->translate('involved_intro_title')); ?></h1>
        <p><?php echo htmlspecialchars($lang->translate('involved_intro_text')); ?></p>
    </article>

    <!-- Grid container for responsive layout -->
    <div class="grid" style="margin-top: 2rem; gap: 2rem;">
        <!-- Join Event (first) -->
        <article style="padding: 1rem; display: flex; flex-direction: column;">
            <div>
                <h2><?php echo htmlspecialchars($lang->translate('join_event_title')); ?></h2>
                <p style="margin: 0.5rem 0;"><?php echo htmlspecialchars($lang->translate('join_event_description')); ?></p>
            </div>
            <form method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/join" style="margin-top: auto; padding-top: 1.5rem;">
                <select id="remembered-events" style="width: 100%; margin-bottom: 0.8rem;">
                    <option value="default"><?php echo htmlspecialchars($lang->translate('my_events')); ?></option>
                    <!-- Options will be populated via JavaScript -->
                </select>
                
                <input id="event-code" name="event_code" placeholder="<?php echo htmlspecialchars($lang->translate('event_code_placeholder')); ?>" required maxlength="6" minlength="6" pattern="[A-Z0-9]{6}" style="width: 100%;">
                
                <button class="primary" type="submit" style="margin-top: 1rem; width: 100%;"><?php echo htmlspecialchars($lang->translate('join_event_button')); ?></button>
            </form>
        </article>

        <!-- Create Event (second) -->
        <article style="padding: 1rem; display: flex; flex-direction: column;">
            <div>
                <h2><?php echo htmlspecialchars($lang->translate('create_event_title')); ?></h2>
                <p style="margin: 0.5rem 0;"><?php echo htmlspecialchars($lang->translate('create_event_description')); ?></p>
            </div>
            <form id="create-event-form" method="post" action="/<?php echo htmlspecialchars($lang->getCurrentLanguage()); ?>/involved/create" style="margin-top: auto; padding-top: 1.5rem;">
                <input id="create-password" name="password" type="password" placeholder="<?php echo htmlspecialchars($lang->translate('password_placeholder')); ?>" style="width: 100%;">
                
                <button class="primary" type="submit" style="margin-top: 1rem; width: 100%;"><?php echo htmlspecialchars($lang->translate('create_event_button')); ?></button>
            </form>
        </article>
    </div>
</main>

<!-- Script for handling event memory -->
<script src="/apps/involved/js/eventRememberHelper.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const helper = new EventRememberHelper();
    const eventSelect = document.getElementById('remembered-events');
    const eventCodeInput = document.getElementById('event-code');
    const createEventForm = document.getElementById('create-event-form');
    
    // Populate the dropdown with remembered events
    function populateEventSelect() {
        // Clear existing options (except default)
        while (eventSelect.options.length > 1) {
            eventSelect.remove(1);
        }
        
        // Get events from storage
        const events = helper.getStoredEvents();
        events.forEach(event => {
            const option = document.createElement('option');
            option.value = event.code;
            option.textContent = `${event.code} (${helper.formatDate(event.created)})`;
            eventSelect.appendChild(option);
        });
        
        // Always start with default option selected
        eventSelect.value = 'default';
    }
    
    // Initialize the dropdown
    populateEventSelect();
    
    // Handle selection change
    eventSelect.addEventListener('change', function() {
        const selected = this.value;
        if (selected && selected !== 'default') {
            eventCodeInput.value = selected;
            helper.setLastSelected(selected);
        } else {
            eventCodeInput.value = '';
        }
    });
    
    // Reset dropdown to default when typing in the input
    eventCodeInput.addEventListener('input', function() {
        eventSelect.value = 'default';
    });
    
    // Store newly created events
    if (createEventForm) {
        // We can't directly capture the code here as it's generated server-side
        // Storing will happen when redirected to the event page
        
        // Check if we were redirected from event creation
        const urlParams = new URLSearchParams(window.location.search);
        const createdEvent = urlParams.get('created');
        if (createdEvent) {
            helper.addEvent(createdEvent);
            // Remove the parameter to avoid adding multiple times on refresh
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }
});
</script>

<?php require_once __DIR__ . '/../../../views/footer.php'; ?>
