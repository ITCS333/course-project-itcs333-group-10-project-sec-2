/*
  Requirement: Populate the single topic page and manage replies.

  Instructions:
  1. Link this file to `topic.html` using:
     <script src="topic.js" defer></script>

  2. In `topic.html`, add the following IDs:
     - To the <h1>: `id="topic-subject"`
     - To the <article id="original-post">:
       - Add a <p> with `id="op-message"` for the message text.
       - Add a <footer> with `id="op-footer"` for the metadata.
     - To the <div> for the list of replies: `id="reply-list-container"`
     - To the "Post a Reply" <form>: `id="reply-form"`

  3. Implement the TODOs below.
*/

// --- Global Data Store ---
let currentTopicId = null;
let currentReplies = []; // Will hold replies for *this* topic

// --- Element Selections ---
// TODO: Select all the elements you added IDs for in step 2.
const topicSubject = document.querySelector('#topic-subject');
const opMessage = document.querySelector('#op-message');
const opFooter = document.querySelector('#op-footer');
const replyListContainer = document.querySelector('#reply-list-container');
const replyForm = document.querySelector('#reply-form');
const newReplyText = document.querySelector('#new-reply');
const originalPostArticle = document.querySelector('#original-post');

// --- Functions ---

/**
 * TODO: Implement the getTopicIdFromURL function.
 * It should:
 * 1. Get the query string from `window.location.search`.
 * 2. Use the `URLSearchParams` object to get the value of the 'id' parameter.
 * 3. Return the id.
 */
function getTopicIdFromURL() {
  // 1. Get the query string from `window.location.search`.
  const queryString = window.location.search;

  // 2. Use the `URLSearchParams` object to get the value of the 'id' parameter.
  const params = new URLSearchParams(queryString);
  const id = params.get('id');

  // 3. Return the id.
  return id;
}

/**
 * TODO: Implement the renderOriginalPost function.
 * It takes one topic object.
 * It should:
 * 1. Set the `textContent` of `topicSubject` to the topic's subject.
 * 2. Set the `textContent` of `opMessage` to the topic's message.
 * 3. Set the `textContent` of `opFooter` to "Posted by: {author} on {date}".
 * 4. (Optional) Add a "Delete" button with `data-id="${topic.id}"` to the OP.
 */
function renderOriginalPost(topic) {
  if (!topic) return;

  // 1. Set the `textContent` of `topicSubject` to the topic's subject.
  if (topicSubject) {
    topicSubject.textContent = topic.subject || 'Untitled Topic';
  }

  // 2. Set the `textContent` of `opMessage` to the topic's message.
  if (opMessage) {
    opMessage.textContent = topic.message || '';
  }

  // 3. Set the `textContent` of `opFooter` to "Posted by: {author} on {date}".
  if (opFooter) {
    const author = topic.author || 'Unknown';
    const date = topic.date || 'Unknown date';
    opFooter.textContent = `Posted by: ${author} on ${date}`;
  }

  // 4. (Optional) Add a "Delete" button with `data-id="${topic.id}"` to the OP.
  if (originalPostArticle) {
    let actionsDiv = originalPostArticle.querySelector('.op-actions');
    if (!actionsDiv) {
      actionsDiv = document.createElement('div');
      actionsDiv.classList.add('op-actions');

      const deleteBtn = document.createElement('button');
      deleteBtn.type = 'button';
      deleteBtn.textContent = 'Delete';
      deleteBtn.classList.add('delete-op-btn');
      deleteBtn.dataset.id = topic.id;

      actionsDiv.appendChild(deleteBtn);
      originalPostArticle.appendChild(actionsDiv);
    } else {
      const deleteBtn = actionsDiv.querySelector('.delete-op-btn');
      if (deleteBtn) {
        deleteBtn.dataset.id = topic.id;
      }
    }
  }
}

/**
 * TODO: Implement the createReplyArticle function.
 * It takes one reply object {id, author, date, text}.
 * It should return an <article> element matching the structure in `topic.html`.
 * - Include a <p> for the `text`.
 * - Include a <footer> for the `author` and `date`.
 * - Include a "Delete" button with class "delete-reply-btn" and `data-id="${id}"`.
 */
function createReplyArticle(reply) {
  const article = document.createElement('article');
  article.classList.add('reply');

  // <p> for the text
  const p = document.createElement('p');
  p.textContent = reply.text || '';
  article.appendChild(p);

  // <footer> for author and date
  const footer = document.createElement('footer');
  const author = reply.author || 'Unknown';
  const date = reply.date || 'Unknown date';
  footer.textContent = `Posted by: ${author} on ${date}`;
  article.appendChild(footer);

  // Actions container with delete button
  const actionsDiv = document.createElement('div');
  actionsDiv.classList.add('reply-actions');

  const deleteBtn = document.createElement('button');
  deleteBtn.type = 'button';
  deleteBtn.textContent = 'Delete';
  deleteBtn.classList.add('delete-reply-btn');
  deleteBtn.dataset.id = reply.id;

  actionsDiv.appendChild(deleteBtn);
  article.appendChild(actionsDiv);

  return article;
}

/**
 * TODO: Implement the renderReplies function.
 * It should:
 * 1. Clear the `replyListContainer`.
 * 2. Loop through the global `currentReplies` array.
 * 3. For each reply, call `createReplyArticle()`, and
 * append the resulting <article> to `replyListContainer`.
 */
function renderReplies() {
  if (!replyListContainer) return;

  // 1. Clear the `replyListContainer`.
  replyListContainer.innerHTML = '';

  // 2. Loop through the global `currentReplies` array.
  currentReplies.forEach((reply) => {
    // 3. Create and append.
    const article = createReplyArticle(reply);
    replyListContainer.appendChild(article);
  });
}

/**
 * TODO: Implement the handleAddReply function.
 * This is the event handler for the `replyForm` 'submit' event.
 * It should:
 * 1. Prevent the form's default submission.
 * 2. Get the text from `newReplyText.value`.
 * 3. If the text is empty, return.
 * 4. Create a new reply object:
 * {
 * id: `reply_${Date.now()}`,
 * author: 'Student' (hardcoded),
 * date: new Date().toISOString().split('T')[0],
 * text: (reply text value)
 * }
 * 5. Add this new reply to the global `currentReplies` array (in-memory only).
 * 6. Call `renderReplies()` to refresh the list.
 * 7. Clear the `newReplyText` textarea.
 */
function handleAddReply(event) {
  // 1. Prevent the form's default submission.
  event.preventDefault();

  if (!newReplyText) return;

  // 2. Get the text from `newReplyText.value`.
  const text = newReplyText.value.trim();

  // 3. If the text is empty, return.
  if (!text) {
    return;
  }

  // 4. Create a new reply object.
  const newReply = {
    id: `reply_${Date.now()}`,
    author: 'Student', // hardcoded
    date: new Date().toISOString().split('T')[0],
    text: text
  };

  // 5. Add this new reply to the global `currentReplies` array (in-memory only).
  currentReplies.push(newReply);

  // 6. Call `renderReplies()` to refresh the list.
  renderReplies();

  // 7. Clear the `newReplyText` textarea.
  newReplyText.value = '';
}

/**
 * TODO: Implement the handleReplyListClick function.
 * This is an event listener on the `replyListContainer` (for delegation).
 * It should:
 * 1. Check if the clicked element (`event.target`) has the class "delete-reply-btn".
 * 2. If it does, get the `data-id` attribute from the button.
 * 3. Update the global `currentReplies` array by filtering out the reply
 * with the matching ID (in-memory only).
 * 4. Call `renderReplies()` to refresh the list.
 */
function handleReplyListClick(event) {
  const target = event.target;

  // 1. Check if the clicked element (`event.target`) has the class "delete-reply-btn".
  if (!target.classList.contains('delete-reply-btn')) {
    return;
  }

  // 2. Get the `data-id` attribute from the button.
  const replyId = target.dataset.id;

  // 3. Filter out the matching reply.
  currentReplies = currentReplies.filter((reply) => reply.id !== replyId);

  // 4. Refresh the list.
  renderReplies();
}

/**
 * TODO: Implement an `initializePage` function.
 * This function needs to be 'async'.
 * It should:
 * 1. Get the `currentTopicId` by calling `getTopicIdFromURL()`.
 * 2. If no ID is found, set `topicSubject.textContent = "Topic not found."` and stop.
 * 3. `fetch` both 'topics.json' and 'replies.json' (you can use `Promise.all`).
 * 4. Parse both JSON responses.
 * 5. Find the correct topic from the topics array using the `currentTopicId`.
 * 6. Get the correct replies array from the replies object using the `currentTopicId`.
 * Store this in the global `currentReplies` variable. (If no replies exist, use an empty array).
 * 7. If the topic is found:
 * - Call `renderOriginalPost()` with the topic object.
 * - Call `renderReplies()` to show the initial replies.
 * - Add the 'submit' event listener to `replyForm` (calls `handleAddReply`).
 * - Add the 'click' event listener to `replyListContainer` (calls `handleReplyListClick`).
 * 8. If the topic is not found, display an error in `topicSubject`.
 */
async function initializePage() {
  // 1. Get the `currentTopicId` by calling `getTopicIdFromURL()`.
  currentTopicId = getTopicIdFromURL();

  // 2. If no ID is found, set `topicSubject.textContent = "Topic not found."` and stop.
  if (!currentTopicId) {
    if (topicSubject) {
      topicSubject.textContent = 'Topic not found.';
    }
    return;
  }

  try {
    // 3. `fetch` both 'topics.json' and 'replies.json' (here: comments.json) using Promise.all.
    // In this project, JSON files are in the 'api' folder:
    // - topics.json
    // - comments.json (used instead of replies.json)
    const [topicsResponse, commentsResponse] = await Promise.all([
      fetch('api/topics.json'),
      fetch('api/comments.json')
    ]);

    // 4. Parse both JSON responses.
    const topicsData = topicsResponse.ok ? await topicsResponse.json() : [];
    const commentsData = commentsResponse.ok ? await commentsResponse.json() : {};

    // 5. Find the correct topic from the topics array using the `currentTopicId`.
    const topic = Array.isArray(topicsData)
      ? topicsData.find((t) => t.id === currentTopicId)
      : null;

    // 6. Get the correct replies array from the replies object using the `currentTopicId`.
    // Store this in the global `currentReplies` variable. (If no replies exist, use an empty array).
    currentReplies = commentsData[currentTopicId] || [];

    // 7. If the topic is found, render and attach events.
    if (topic) {
      // - Call `renderOriginalPost()` with the topic object.
      renderOriginalPost(topic);
      // - Call `renderReplies()` to show the initial replies.
      renderReplies();
      // - Add the 'submit' event listener to `replyForm` (calls `handleAddReply`).
      if (replyForm) {
        replyForm.addEventListener('submit', handleAddReply);
      }
      // - Add the 'click' event listener to `replyListContainer` (calls `handleReplyListClick`).
      if (replyListContainer) {
        replyListContainer.addEventListener('click', handleReplyListClick);
      }
    } else {
      // 8. If the topic is not found, display an error in `topicSubject`.
      if (topicSubject) {
        topicSubject.textContent = 'Topic not found.';
      }
    }
  } catch (error) {
    console.error('Error initializing topic page:', error);
    if (topicSubject) {
      topicSubject.textContent = 'An error occurred while loading the topic.';
    }
  }
}

// --- Initial Page Load ---
initializePage();
