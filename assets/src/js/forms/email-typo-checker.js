/**
 * Email Domain Typo Checker
 * 
 * Detects common typos in email domains and suggests corrections
 * using Levenshtein distance algorithm (similar to the abandoned Mailcheck library)
 */

// Common email domains to check against
const COMMON_DOMAINS = [
  'gmail.com',
  'yahoo.com',
  'hotmail.com',
  'outlook.com',
  'icloud.com',
  'aol.com',
  'live.com',
  'msn.com',
  'me.com',
  'mac.com',
  'googlemail.com',
  'ymail.com',
  'protonmail.com',
  'mail.com',
  'gmx.com',
  'zoho.com'
]

/**
 * Calculate Levenshtein distance between two strings
 * @param {string} a - First string
 * @param {string} b - Second string
 * @returns {number} Edit distance between the strings
 */
function levenshteinDistance(a, b) {
  if (a.length === 0) return b.length
  if (b.length === 0) return a.length

  const matrix = []

  // Initialize first column
  for (let i = 0; i <= b.length; i++) {
    matrix[i] = [i]
  }

  // Initialize first row
  for (let j = 0; j <= a.length; j++) {
    matrix[0][j] = j
  }

  // Fill in the rest of the matrix
  for (let i = 1; i <= b.length; i++) {
    for (let j = 1; j <= a.length; j++) {
      if (b.charAt(i - 1) === a.charAt(j - 1)) {
        matrix[i][j] = matrix[i - 1][j - 1]
      } else {
        matrix[i][j] = Math.min(
          matrix[i - 1][j - 1] + 1, // substitution
          matrix[i][j - 1] + 1, // insertion
          matrix[i - 1][j] + 1 // deletion
        )
      }
    }
  }

  return matrix[b.length][a.length]
}

/**
 * Find the closest matching domain from the common domains list
 * @param {string} domain - The domain to check
 * @returns {string|null} Suggested domain or null if no close match found
 */
function findClosestDomain(domain) {
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
function extractDomain(email) {
  const parts = email.split('@')
  return parts.length === 2 ? parts[1] : null
}

/**
 * Create suggestion element
 * @param {string} suggestedEmail - The suggested corrected email
 * @param {HTMLInputElement} emailField - The email input field
 * @returns {HTMLElement} The suggestion element
 */
function createSuggestionElement(suggestedEmail, emailField) {
  const suggestion = document.createElement('div')
  suggestion.className = 'mc4wp-email-suggestion'
  suggestion.style.cssText = 'margin-top: 5px; font-size: 13px; color: #666;'

  const link = document.createElement('a')
  link.href = '#'
  link.style.cssText = 'color: #0073aa; text-decoration: none; cursor: pointer;'

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
function removeSuggestion(emailField) {
  const existingSuggestion = emailField.parentElement.querySelector('.mc4wp-email-suggestion')
  if (existingSuggestion) {
    existingSuggestion.remove()
  }
}

/**
 * Check email for typos and show suggestion if needed
 * @param {HTMLInputElement} emailField - The email input field
 */
function checkEmailTypo(emailField) {
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
    emailField.parentElement.appendChild(suggestion)
  }
}

/**
 * Initialize typo checker for a form
 * @param {HTMLFormElement} formElement - The form element
 */
function initTypoChecker(formElement) {
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
function init() {
  // Find all MC4WP forms with typo checking enabled
  const forms = document.querySelectorAll('.mc4wp-form[data-typo-check="1"]')

  forms.forEach(function (form) {
    initTypoChecker(form)
  })
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init)
} else {
  init()
}

// Export for potential use by other scripts
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { init, checkEmailTypo, levenshteinDistance, findClosestDomain }
}
