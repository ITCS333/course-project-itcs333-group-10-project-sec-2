/*
  Requirement: Populate the resource detail page and discussion forum.
*/

// --- Global Data Store ---
let currentResourceId = null;
let currentComments = [];

// --- Element Selections ---
const resourceTitle = document.querySelector('#resource-title');
const resourceDescription = document.querySelector('#resource-description');
const resourceLink = document.querySelector('#resource-link');
const commentList = document.querySelector('#comment-list');
const commentForm = document.querySelector('#comment-form');
const newComment = document.querySelector('#new-comment');

// --- Functions ---

// Get the resource ID from URL query string
function getResourceIdFromURL() {
  const params = new URLSearchParams(window.location.search);
  return params.get('id');
}

// Render the main resource details
function renderResourceDetails(resource) {
  resourceTitle.textContent = resource.title;
  resourceDescription.textContent = resource.description;
  resourceLink.href = resource.link;
}

// Create an article element for a single comment
function createCommentArticle(comment) {
  const article = document.createElement('article');
  article.classList.add('comment');

  const p = document.createElement('p');
  p.textContent = comment.text;

  const footer = document.createElement('footer');
  footer.textContent = `Posted by: ${comment.author}`;

  article.appendChild(p);
  article.appendChild(footer);

  return article;
}

// Render all comments in the comment list
function renderComments() {
  commentList.innerHTML = '';
  currentComments.forEach(comment => {
    const article = createCommentArticle(comment);
    commentList.appendChild(article);
  });
}

// Handle adding a new comment
function handleAddComment(event) {
  event.preventDefault();
  const commentText = newComment.value.trim();
  if (!commentText) return;

  const commentObj = {
    author: 'Student',
    text: commentText
  };

  currentComments.push(commentObj);
  renderComments();
  newComment.value = '';
}

// Initialize the page
async function initializePage() {
  currentResourceId = getResourceIdFromURL();
  if (!currentResourceId) {
    resourceTitle.textContent = "Resource not found.";
    return;
  }

  try {
    const [resourcesRes, commentsRes] = await Promise.all([
      fetch('resources.json'),
      fetch('resource-comments.json')
    ]);

    if (!resourcesRes.ok || !commentsRes.ok) throw new Error('Failed to fetch data');

    const resources = await resourcesRes.json();
    const allComments = await commentsRes.json();

    const resource = resources.find(r => r.id === currentResourceId);
    currentComments = allComments[currentResourceId] || [];

    if (resource) {
      renderResourceDetails(resource);
      renderComments();
      commentForm.addEventListener('submit', handleAddComment);
    } else {
      resourceTitle.textContent = "Resource not found.";
    }

  } catch (error) {
    console.error(error);
    resourceTitle.textContent = "Error loading resource.";
  }
}

// --- Initial Page Load ---
initializePage();
