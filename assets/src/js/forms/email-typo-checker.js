/**
 * Email Domain Typo Checker
 *
 * Detects common typos in email domains and suggests corrections
 * using Levenshtein distance algorithm (similar to the abandoned Mailcheck library)
 */

// Common email domains to check against
const COMMON_DOMAINS = (window.mc4wp_email_typo_checker && window.mc4wp_email_typo_checker.domains)
  ? window.mc4wp_email_typo_checker.domains
  : []

/**
 * Calculate Levenshtein distance between two strings
 * Optimized implementation using single vector instead of full matrix
 * @param {string} a - First string
 * @param {string} b - Second string
 * @returns {number} Edit distance between the strings
 */
function levenshteinDistance (a, b) {
  // Early exit for identical strings
  if (a === b) {
    return 0
  }

  // Ensure a is the shorter string for optimization
  if (a.length > b.length) {
    const tmp = a
    a = b
    b = tmp
  }

  let la = a.length
  let lb = b.length

  // Strip common suffix
  while (la > 0 && (a.charCodeAt(la - 1) === b.charCodeAt(lb - 1))) {
    la--
    lb--
  }

  // Strip common prefix
  let offset = 0
  while (offset < la && (a.charCodeAt(offset) === b.charCodeAt(offset))) {
    offset++
  }

  la -= offset
  lb -= offset

  if (la === 0 || lb < 3) {
    return lb
  }

  let x = 0
  let y
  let d0
  let d1
  let d2
  let d3
  let dd
  let dy
  let ay
  let bx0
  let bx1
  let bx2
  let bx3

  const vector = []

  for (y = 0; y < la; y++) {
    vector.push(y + 1)
    vector.push(a.charCodeAt(offset + y))
  }

  const len = vector.length - 1

  // Process 4 characters at a time
  for (; x < lb - 3;) {
    bx0 = b.charCodeAt(offset + (d0 = x))
    bx1 = b.charCodeAt(offset + (d1 = x + 1))
    bx2 = b.charCodeAt(offset + (d2 = x + 2))
    bx3 = b.charCodeAt(offset + (d3 = x + 3))
    dd = (x += 4)
    for (y = 0; y < len; y += 2) {
      dy = vector[y]
      ay = vector[y + 1]
      d0 = _min(dy, d0, d1, bx0, ay)
      d1 = _min(d0, d1, d2, bx1, ay)
      d2 = _min(d1, d2, d3, bx2, ay)
      dd = _min(d2, d3, dd, bx3, ay)
      vector[y] = dd
      d3 = d2
      d2 = d1
      d1 = d0
      d0 = dy
    }
  }

  // Process remaining characters
  for (; x < lb;) {
    bx0 = b.charCodeAt(offset + (d0 = x))
    dd = ++x
    for (y = 0; y < len; y += 2) {
      dy = vector[y]
      vector[y] = dd = _min(dy, d0, dd, bx0, vector[y + 1])
      d0 = dy
    }
  }

  return dd
}

/**
 * Helper function for calculating minimum distance
 * @private
 */
function _min (d0, d1, d2, bx, ay) {
  return d0 < d1 || d2 < d1
    ? d0 > d2
      ? d2 + 1
      : d0 + 1
    : bx === ay
      ? d1
      : d1 + 1
}

/**
 * Find the closest matching domain from the common domains list
 * @param {string} domain - The domain to check
 * @returns {string|null} Suggested domain or null if no close match found
 */
function findClosestDomain (domain) {
  if (!domain) return null

  const domainLower = domain.toLowerCase()
  let minDistance = Infinity
  let closestDomain = null

  // If exact match, no suggestion needed
  if (COMMON_DOMAINS.includes(domainLower)) {
    return null
  }

  for (let i = 0; i < COMMON_DOMAINS.length; i++) {
    const commonDomain = COMMON_DOMAINS[i]
    const distance = levenshteinDistance(domainLower, commonDomain)

    // Only suggest if distance is 1 or 2 (1-2 character edits)
    // and it's the closest match so far
    if (distance > 0 && distance <= 2 && distance < minDistance) {
      minDistance = distance
      closestDomain = commonDomain
    }
  }

  return closestDomain
}

/**
 * Extract domain from email address
 * @param {string} email - Email address
 * @returns {string|null} Domain part of email or null
 */
function extractDomain (email) {
  const parts = email.split('@')
  return parts.length === 2 ? parts[1] : null
}

/**
 * Create suggestion element
 * @param {string} suggestedEmail - The suggested corrected email
 * @param {HTMLInputElement} emailField - The email input field
 * @returns {HTMLElement} The suggestion element
 */
function createSuggestionElement (suggestedEmail, emailField) {
  const suggestion = document.createElement('div')
  suggestion.className = 'mc4wp-email-suggestion'

  const link = document.createElement('a')
  link.href = '#'

  // Use translatable string from WordPress
  const suggestionText = window.mc4wp_email_typo_checker && window.mc4wp_email_typo_checker.suggestion_text
    ? window.mc4wp_email_typo_checker.suggestion_text
    : 'Did you mean %s?'
  link.textContent = suggestionText.replace('%s', suggestedEmail)

  link.addEventListener('mousedown', function (e) {
    e.preventDefault()
    emailField.value = suggestedEmail
    removeSuggestion(emailField)
    // Trigger change event so other scripts can react
    const event = new Event('change', { bubbles: true })
    emailField.dispatchEvent(event)
  })

  suggestion.appendChild(link)
  return suggestion
}

/**
 * Remove existing suggestion element
 * @param {HTMLInputElement} emailField - The email input field
 */
function removeSuggestion (emailField) {
  const existingSuggestion = emailField.parentElement.querySelector('.mc4wp-email-suggestion')
  if (existingSuggestion) {
    existingSuggestion.remove()
  }
}

/**
 * Check email for typos and show suggestion if needed
 * @param {HTMLInputElement} emailField - The email input field
 */
function checkEmailTypo (emailField) {
  const email = emailField.value.trim()

  // Remove any existing suggestion first
  removeSuggestion(emailField)

  // Need at least an @ symbol to check
  if (!email || email.indexOf('@') === -1) {
    return
  }

  const domain = extractDomain(email)
  if (!domain) {
    return
  }

  const suggestedDomain = findClosestDomain(domain)
  if (suggestedDomain) {
    const emailParts = email.split('@')
    const suggestedEmail = emailParts[0] + '@' + suggestedDomain

    const suggestion = createSuggestionElement(suggestedEmail, emailField)
    emailField.after(suggestion)
  }
}

/**
 * Initialize typo checker for a form
 * @param {HTMLFormElement} formElement - The form element
 */
function initTypoChecker (formElement) {
  // Find all email fields in the form
  const emailFields = formElement.querySelectorAll('input[type="email"]')

  emailFields.forEach(function (emailField) {
    // Add keyup event listener
    emailField.addEventListener('keyup', function () {
      checkEmailTypo(emailField)
    })

    // Also check on blur to catch paste events
    emailField.addEventListener('blur', function () {
      checkEmailTypo(emailField)
    })
  })
}

/**
 * Initialize on all MC4WP forms with typo checking enabled
 */
function init () {
  // Find all MC4WP forms with typo checking enabled
  const forms = document.querySelectorAll('.mc4wp-form[data-typo-check="1"]')

  forms.forEach(initTypoChecker)
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', init)

// Export for potential use by other scripts
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { init, checkEmailTypo, levenshteinDistance, findClosestDomain }
}
