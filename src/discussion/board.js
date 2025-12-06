/*
  Requirement: Make the "Discussion Board" page interactive.

  Instructions:
  1. Link this file to `board.html` (or `baord.html`) using:
     <script src="board.js" defer></script>
  
  2. In `board.html`, add an `id="topic-list-container"` to the 'div'
     that holds the list of topic articles.
  
  3. Implement the TODOs below.
*/

// --- Global Data Store ---
// This will hold the topics loaded from the JSON file.
let topics = [];

// --- Element Selections ---
// TODO: Select the new topic form ('#new-topic-form').
const newTopicForm = document.querySelector('#new-topic-form');

// TODO: Select the topic list container ('#topic-list-container').
const topicListContainer = document.querySelector('#topic-list-container');

// --- Functions ---

/**
 * TODO: Implement the createTopicArticle function.
 * It takes one topic object {id, subject, author, date}.
 * It should return an <article> element matching the structure in `board.html`.
 * - The main link's `href` MUST be `topic.html?id=${id}`.
 * - The footer should contain the author and date.
 * - The actions div should contain an "Edit" button and a "Delete" button.
 * - The "Delete" button should have a class "delete-btn" and `data-id="${id}"`.
 */
function createTopicArticle(topic) {
  // Create main article element
  const article = document.createElement('article');
  article.classList.add('topic');

  // Create heading with link
  const h3 = document.createElement('h3');
  const link = document.createElement('a');
  // The main link's `href` MUST be `topic.html?id=${id}`.
  link.href = `topic.html?id=${encodeURIComponent(topic.id)}`;
  link.textContent = topic.subject || 'Untitled Topic';
  h3.appendChild(link);

  // Footer with author and date
  const footer = document.createElement('footer');
  const author = topic.author || 'Unknown';
  const date = topic.date || 'Unknown date';
  footer.textContent = `Posted by: ${author} on ${date}`;

  // Actions container
  const actionsDiv = document.createElement('div');
  actionsDiv.classList.add('topic-actions');

  const editBtn = document.createElement('button');
  editBtn.type = 'button';
  editBtn.textContent = 'Edit';
  editBtn.classList.add('edit-topic');

  const deleteBtn = document.createElement('button');
  deleteBtn.type = 'button';
  deleteBtn.textContent = 'Delete';
  // The "Delete" button should have a class "delete-btn" and `data-id="${id}"`.
  deleteBtn.classList.add('delete-btn');
  deleteBtn.dataset.id = topic.id;

  actionsDiv.appendChild(editBtn);
  actionsDiv.appendChild(deleteBtn);

  // Assemble article
  article.appendChild(h3);
  article.appendChild(footer);
  article.appendChild(actionsDiv);

  return article;
}

/**
 * TODO: Implement the renderTopics function.
 * It should:
 * 1. Clear the `topicListContainer`.
 * 2. Loop through the global `topics` array.
 * 3. For each topic, call `createTopicArticle()`, and
 * append the resulting <article> to `topicListContainer`.
 */
function renderTopics() {
  if (!topicListContainer) return;

  // 1. Clear the `topicListContainer`.
  topicListContainer.innerHTML = '';

  // 2. Loop through the global `topics` array.
  topics.forEach((topic) => {
    // 3. Create article and append it
    const article = createTopicArticle(topic);
    topicListContainer.appendChild(article);
  });
}

/**
 * TODO: Implement the handleCreateTopic function.
 * This is the event handler for the form's 'submit' event.
 * It should:
 * 1. Prevent the form's default submission.
 * 2. Get the values from the '#topic-subject' and '#topic-message' inputs.
 * 3. Create a new topic object with the structure:
 * {
 * id: `topic_${Date.now()}`,
 * subject: (subject value),
 * message: (message value),
 * author: 'Student' (use a hardcoded author for this exercise),
 * date: new Date().toISOString().split('T')[0] // Gets today's date YYYY-MM-DD
 * }
 * 4. Add this new topic object to the global `topics` array (in-memory only).
 * 5. Call `renderTopics()` to refresh the list.
 * 6. Reset the form.
 */
function handleCreateTopic(event) {
  // 1. Prevent the form's default submission.
  event.preventDefault();

  const subjectInput = document.querySelector('#topic-subject');
  const messageInput = document.querySelector('#topic-message');

  if (!subjectInput || !messageInput) return;

  // 2. Get the values from the inputs.
  const subject = subjectInput.value.trim();
  const message = messageInput.value.trim();

  if (!subject || !message) {
    alert('Please fill in both subject and message.');
    return;
  }

  // 3. Create a new topic object.
  const newTopic = {
    id: `topic_${Date.now()}`,
    subject: subject,
    message: message,
    author: 'Student', // hardcoded author for this exercise
    date: new Date().toISOString().split('T')[0]
  };

  // 4. Add this new topic object to the global `topics` array (in-memory only).
  topics.push(newTopic);

  // 5. Call `renderTopics()` to refresh the list.
  renderTopics();

  // 6. Reset the form.
  newTopicForm.reset();
}

/**
 * TODO: Implement the handleTopicListClick function.
 * This is an event listener on the `topicListContainer` (for delegation).
 * It should:
 * 1. Check if the clicked element (`event.target`) has the class "delete-btn".
 * 2. If it does, get the `data-id` attribute from the button.
 * 3. Update the global `topics` array by filtering out the topic
 * with the matching ID (in-memory only).
 * 4. Call `renderTopics()` to refresh the list.
 */
function handleTopicListClick(event) {
  const target = event.target;

  // 1. Check if the clicked element (`event.target`) has the class "delete-btn".
  if (!target.classList.contains('delete-btn')) {
    return;
  }

  // 2. Get the `data-id` attribute from the button.
  const topicId = target.dataset.id;

  // 3. Filter out the matching topic.
  topics = topics.filter((topic) => topic.id !== topicId);

  // 4. Refresh the list.
  renderTopics();
}

/**
 * TODO: Implement the loadAndInitialize function.
 * This function needs to be 'async'.
 * It should:
 * 1. Use `fetch()` to get data from 'topics.json'.
 * 2. Parse the JSON response and store the result in the global `topics` array.
 * 3. Call `renderTopics()` to populate the list for the first time.
 * 4. Add the 'submit' event listener to `newTopicForm` (calls `handleCreateTopic`).
 * 5. Add the 'click' event listener to `topicListContainer` (calls `handleTopicListClick`).
 */
async function loadAndInitialize() {
  try {
    // 1. Use `fetch()` to get data from 'topics.json'.
    // In this project, topics.json is inside the 'api' folder.
    const response = await fetch('api/topics.json');

    if (!response.ok) {
      console.warn('Could not load api/topics.json, starting with empty topics.');
      topics = [];
    } else {
      // 2. Parse the JSON response and store in `topics`.
      const data = await response.json();
      topics = Array.isArray(data) ? data : [];
    }
  } catch (error) {
    console.error('Error loading topics:', error);
    topics = [];
  }

  // 3. Render them initially.
  renderTopics();

  // 4. Add the 'submit' event listener to `newTopicForm`.
  if (newTopicForm) {
    newTopicForm.addEventListener('submit', handleCreateTopic);
  }

  // 5. Add the 'click' event listener to `topicListContainer`.
  if (topicListContainer) {
    topicListContainer.addEventListener('click', handleTopicListClick);
  }
}

// --- Initial Page Load ---
// Call the main async function to start the application.
loadAndInitialize();
