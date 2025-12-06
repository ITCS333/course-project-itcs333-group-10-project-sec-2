/*
  Requirement: Populate the weekly detail page and discussion forum.

  Instructions:
  1. Link this file to `details.html` using:
     <script src="details.js" defer></script>

  2. In `details.html`, add the following IDs:
     - To the <h1>: `id="week-title"`
     - To the start date <p>: `id="week-start-date"`
     - To the description <p>: `id="week-description"`
     - To the "Exercises & Resources" <ul>: `id="week-links-list"`
     - To the <div> for comments: `id="comment-list"`
     - To the "Ask a Question" <form>: `id="comment-form"`
     - To the <textarea>: `id="new-comment-text"`

  3. Implement the TODOs below.
*/

// --- Global Data Store ---
let currentWeekId = null;
let currentComments = [];

// --- Element Selections ---
const weekTitle = document.querySelector('#week-title');
const weekStartDate = document.querySelector('#week-start-date');
const weekDescription = document.querySelector('#week-description');
const weekLinksList = document.querySelector('#week-links-list');
const commentList = document.querySelector('#comment-list');
const commentForm = document.querySelector('#comment-form');
const newCommentText = document.querySelector('#new-comment-text');

/**
 * TODO: Implement the getWeekIdFromURL function.
 * It should:
 * 1. Get the query string from `window.location.search`.
 * 2. Use the `URLSearchParams` object to get the value of the 'id' parameter.
 * 3. Return the id.
 */
function getWeekIdFromURL() {
    const params = new URLSearchParams(window.location.search);
    return params.get('id');
}

/**
 * TODO: Implement the renderWeekDetails function.
 * It takes one week object.
 * It should:
 * 1. Set the `textContent` of `weekTitle` to the week's title.
 * 2. Set the `textContent` of `weekStartDate` to "Starts on: " + week's startDate.
 * 3. Set the `textContent` of `weekDescription`.
 * 4. Clear `weekLinksList` and then create and append `<li><a href="...">...</a></li>`
 * for each link in the week's 'links' array. The link's `href` and `textContent`
 * should both be the link URL.
 */
function renderWeekDetails(week) {
    weekTitle.textContent = week.title;
    weekStartDate.textContent = `Starts on: ${week.startDate}`;
    weekDescription.textContent = week.description;

    weekLinksList.innerHTML = '';
    if (week.links && week.links.length > 0) {
        week.links.forEach(link => {
            const li = document.createElement('li');
            const a = document.createElement('a');
            
            // FIX: Assumes link is an object {url, text} for clear display text
            a.href = link;
            a.textContent = link; 
            
            li.appendChild(a);
            weekLinksList.appendChild(li);
        });
    }
}

/**
 * TODO: Implement the createCommentArticle function.
 * It takes one comment object {author, text}.
 * It should return an <article> element matching the structure in `details.html`.
 * (e.g., an <article> containing a <p> and a <footer>).
 */
function createCommentArticle(comment) {
    const article = document.createElement('article');

    const p = document.createElement('p');
    p.textContent = comment.text;
    article.appendChild(p);

    const footer = document.createElement('footer');
    footer.textContent = `Posted by: ${comment.author}`;
    article.appendChild(footer);

    return article;
}

/**
 * TODO: Implement the renderComments function.
 * It should:
 * 1. Clear the `commentList`.
 * 2. Loop through the global `currentComments` array.
 * 3. For each comment, call `createCommentArticle()`, and
 * append the resulting <article> to `commentList`.
 */
function renderComments() {
    commentList.innerHTML = '';
    currentComments.forEach(comment => {
        const article = createCommentArticle(comment);
        commentList.appendChild(article);
    });
}

/**
 * TODO: Implement the handleAddComment function.
 * This is the event handler for the `commentForm` 'submit' event.
 * It should:
 * 1. Prevent the form's default submission.
 * 2. Get the text from `newCommentText.value`.
 * 3. If the text is empty, return.
 * 4. Create a new comment object: { author: 'Student', text: commentText }
 * (For this exercise, 'Student' is a fine hardcoded author).
 * 5. Add the new comment to the global `currentComments` array (in-memory only).
 * 6. Call `renderComments()` to refresh the list.
 * 7. Clear the `newCommentText` textarea.
 */
function handleAddComment(event) {
    event.preventDefault();

    const commentText = newCommentText.value.trim();
    if (!commentText) return;

    const newComment = { author: 'Student', text: commentText };
    currentComments.push(newComment);

    renderComments();
    newCommentText.value = '';
}

/**
 * TODO: Implement an `initializePage` function.
 * This function needs to be 'async'.
 * It should:
 * 1. Get the `currentWeekId` by calling `getWeekIdFromURL()`.
 * 2. If no ID is found, set `weekTitle.textContent = "Week not found."` and stop.
 * 3. `fetch` both 'weeks.json' and 'week-comments.json' (you can use `Promise.all`).
 * 4. Parse both JSON responses.
 * 5. Find the correct week from the weeks array using the `currentWeekId`.
 * 6. Get the correct comments array from the comments object using the `currentWeekId`.
 * Store this in the global `currentComments` variable. (If no comments exist, use an empty array).
 * 7. If the week is found:
 * - Call `renderWeekDetails()` with the week object.
 * - Call `renderComments()` to show the initial comments.
 * - Add the 'submit' event listener to `commentForm` (calls `handleAddComment`).
 * 8. If the week is not found, display an error in `weekTitle`.
 */
async function initializePage() {
    currentWeekId = getWeekIdFromURL();

    if (!currentWeekId) {
        weekTitle.textContent = "Week not found.";
        return;
    }

    try {
        const [weeksRes, commentsRes] = await Promise.all([
            fetch('api/weeks.json'),
            fetch('api/week-comments.json')
        ]);

        const weeksData = await weeksRes.json();
        const commentsData = await commentsRes.json();

        // FIX: Ensure ID comparison works by making both sides strings
        const week = weeksData.find(w => String(w.id) === currentWeekId);
        currentComments = commentsData[currentWeekId] || [];

        if (week) {
            renderWeekDetails(week);
            renderComments();
            // The event listener is moved to the bottom initialization section for reliability
        } else {
            weekTitle.textContent = "Week not found.";
        }
    } catch (error) {
        console.error('Error initializing page:', error);
        weekTitle.textContent = "Error loading week data.";
    }
}

// --- Initial Page Load ---
initializePage();

// Event listener is added here to ensure the form is active 
// even if the initializePage function runs into an early exit 
// or if we decide to activate the form before the data fully loads.
commentForm.addEventListener('submit', handleAddComment);
