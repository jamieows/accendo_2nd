// Voice Navigation API for Accendo LMS - Visually Impaired Student Support
// Supports: Speech-to-Text, Text-to-Speech, Intent Parsing, Hands-free Navigation

class VoiceNavigation {
    constructor() {
        // Core state for blind student accessibility
        this.isActive = false;
        this.isListening = false;
        this.isSpeaking = false;
        this.isNavigating = false;
        this.currentPage = this.getCurrentPageName();

        // Track page announcements to prevent repeated greetings
        this.announcedPagesKey = 'announcedPages_' + this.getSessionId();
        this.pageAlreadyAnnounced = this.hasPageBeenAnnounced(this.currentPage);

        // Initialize speech synthesis
        this.synth = window.speechSynthesis;
        this.recognition = null;

        // Global navigation commands - work on ALL pages, silent navigation
        this.commands = {
            // Navigation Commands (silent - announcements happen on destination page)
            'assignments': { action: () => this.navigate('assignments.php'), silent: true },
            'homework': { action: () => this.navigate('assignments.php'), silent: true },
            'exams': { action: () => this.navigate('exams.php'), silent: true },
            'quizzes': { action: () => this.navigate('exams.php'), silent: true },
            'test': { action: () => this.navigate('exams.php'), silent: true },
            'courses': { action: () => this.navigate('my_courses.php'), silent: true },
            'my courses': { action: () => this.navigate('my_courses.php'), silent: true },
            'materials': { action: () => this.navigate('my_courses.php'), silent: true },
            'profile': { action: () => this.navigate('profile.php'), silent: true },
            'dashboard': { action: () => this.navigate('index.php'), silent: true },
            'home': { action: () => this.navigate('index.php'), silent: true },
            'settings': { action: () => this.navigate('settings.php'), silent: true },
            'logout': { action: () => this.navigate('../../Auth/logout.php'), silent: true },
            'log out': { action: () => this.navigate('../../Auth/logout.php'), silent: true },
            'sign out': { action: () => this.navigate('../../Auth/logout.php'), silent: true },

            // Go to commands (explicit global navigation - silent)
            'go to assignments': { action: () => this.navigate('assignments.php'), silent: true },
            'go to my assignments': { action: () => this.navigate('assignments.php'), silent: true },
            'go to exams': { action: () => this.navigate('exams.php'), silent: true },
            'go to my exams': { action: () => this.navigate('exams.php'), silent: true },
            'go to courses': { action: () => this.navigate('my_courses.php'), silent: true },
            'go to my courses': { action: () => this.navigate('my_courses.php'), silent: true },
            'go to profile': { action: () => this.navigate('profile.php'), silent: true },
            'go to my profile': { action: () => this.navigate('profile.php'), silent: true },
            'go to settings': { action: () => this.navigate('settings.php'), silent: true },
            'go to dashboard': { action: () => this.navigate('index.php'), silent: true },
            'go to my dashboard': { action: () => this.navigate('index.php'), silent: true },
            'back to dashboard': { action: () => this.navigate('index.php'), silent: true },
            'go back to dashboard': { action: () => this.navigate('index.php'), silent: true },
            'go back home': { action: () => this.navigate('index.php'), silent: true },
            'go home': { action: () => this.navigate('index.php'), silent: true },

            // Content Reading Commands (work on specific pages - provide response)
            'read stats': { action: () => this.readDashboardStats(), response: 'Reading your dashboard statistics.' },
            'read statistics': { action: () => this.readDashboardStats(), response: 'Here\'s your learning overview.' },
            'read my assignments': { action: () => this.readAssignments(), response: 'Reading your assignments.' },
            'list assignments': { action: () => this.readAssignments(), response: 'Listing your assignments.' },
            'what assignments do i have': { action: () => this.readAssignments(), response: 'Here are your assignments.' },
            'list my pending exams': { action: () => this.speakPendingExams(), response: 'Reading pending exams.' },
            'read missed exams': { action: () => this.speakMissedExams(), response: 'Reading missed exams.' },

            // Control Commands (provide feedback)
            'stop': { action: () => this.deactivate(), response: 'Voice mode stopped.' },
            'exit': { action: () => this.deactivate(), response: 'Voice mode stopped.' },
            'help': { action: () => this.listCommands(), response: 'Here are available commands.' },
            'start listening': { action: () => this.forceStartListening(), response: 'Starting voice recognition.' },
            'enable voice': { action: () => this.forceStartListening(), response: 'Activating voice mode.' },
            'listen': { action: () => this.forceStartListening(), response: 'Now listening for commands.' }
        };

        this.init();
    }

    init() {
        console.log('🔍 Voice Navigation: Starting initialization for blind students...');

        // Check browser support for accessibility critical features
        if (!this.checkBrowserSupport()) {
            console.error('❌ Voice navigation unavailable - browser lacks speech support');
            return;
        }

        console.log('✅ Voice navigation available for accessibility');

        // Initialize speech recognition for command input (critical for blind users)
        this.initSpeechRecognition();

        // Auto-activate on student pages for immediate accessibility
        console.log('🚀 Voice mode activating for student page navigation...');
        this.activate();
    }

    initSpeechRecognition() {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        if (!SpeechRecognition) {
            console.error('❌ Speech recognition not available for voice commands');
            this.speak('Voice recognition not supported in this browser.');
            return;
        }

        try {
            // Create SpeechRecognition instance (works on both modern and legacy browsers)
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.recognition = new SpeechRecognition();

            // Configure for continuous voice navigation
            this.recognition.continuous = true;        // Keep listening continuously
            this.recognition.interimResults = false;   // Only process final results
            this.recognition.lang = 'en-US';           // Language for blind students
            this.recognition.maxAlternatives = 1;      // Single best result

            console.log('✅ Speech Recognition configured for continuous listening');

            // Handle listening start - critical for blind accessibility
            this.recognition.onstart = () => {
                this.isListening = true;
                console.log('🎧 Recognition started');

                // Update visual indicator for sighted assistants
                this.updateVisualIndicator('🎤 Listening...');
            };

            // Handle speech recognition results - main voice command processing
            this.recognition.onresult = (event) => {
                const results = event.results;
                if (!results) return;

                // Find the last completed result
                const lastResultIndex = results.length - 1;
                const lastResult = results[lastResultIndex];

                if (lastResult.isFinal) {
                    const command = lastResult[0].transcript.toLowerCase().trim();
                    const confidence = lastResult[0].confidence;

                    console.log(`🎙️ User said: "${command}" (confidence: ${Math.round(confidence * 100)}%)`);

                    // Process command if confidence is good enough for blind accessibility
                    if (confidence >= 0.5) {
                        // Allow navigation commands even during speech
                        if (this.isNavigationCommand(command) && !this.isCurrentlyReadingPDF) {
                            this.processCommand(command); // Process navigation immediately
                        } else if (!this.isSpeaking) {
                            // Process other commands only when not speaking
                            this.processCommand(command);
                        } else {
                            console.log('🔇 Waiting for speech to finish before processing command');
                            // Queue command to be processed after current speech ends
                            this.queuedCommand = command;
                        }
                    } else {
                        console.log('⚠️ Voice command unclear - ignoring');
                        // Don't give feedback on unclear commands to avoid interrupting flow
                    }
                }
            };

            // Handle recognition ending - restart automatically for continuous access
            this.recognition.onend = () => {
                this.isListening = false;
                console.log('Recognition ended - restarting automatically');

                // Always restart if voice navigation is still active
                if (this.isActive && !this.isSpeaking) {
                    setTimeout(() => {
                        this.startListening();
                        console.log('Recognition restarted');
                    }, 300);
                }
            };

            // Handle recognition errors - provide accessible error feedback to students
            this.recognition.onerror = (event) => {
                console.log(`Error: ${event.error}`);

                // Don't speak error messages as they might interfere with navigation
                // Just log and restart automatically

                // Restart after longer delay on errors
                if (this.isActive) {
                    setTimeout(() => {
                        if (this.isActive) {
                            this.startListening();
                        }
                    }, 2000);
                }
            };

            console.log('🎯 Voice recognition fully initialized - ready for blind student navigation');

        } catch (error) {
            console.error('❌ Critical failure initializing voice recognition:', error);
            this.speak('Voice navigation unavailable. Please contact support.');
        }
    }

    checkBrowserSupport() {
        const speechSupport = !!(window.speechSynthesis);
        const recognitionSupport = !!(window.SpeechRecognition || window.webkitSpeechRecognition);

        console.log('🔍 Browser Support Check:');
        console.log('- Speech Synthesis:', speechSupport ? '✅' : '❌');
        console.log('- Speech Recognition:', recognitionSupport ? '✅' : '❌');
        console.log('- Web Audio API:', !!window.AudioContext ? '✅' : '❌');

        return speechSupport && recognitionSupport;
    }

    checkHTTPS() {
        const isLocal = location.hostname === 'localhost' || location.hostname === '127.0.0.1' || location.hostname === '';
        const isSecure = location.protocol === 'https:' || isLocal;

        console.log('🔐 Security Check:');
        console.log('- Hostname:', location.hostname);
        console.log('- Protocol:', location.protocol);
        console.log('- File context (no hostname):', location.hostname === '');
        console.log('- Secure Context:', isSecure ? '✅' : '❌');

        // Allow to continue but warn about security
        if (!isSecure) {
            console.warn('⚠️ HTTPS/secure context not detected. Voice functions may not work in some browsers.');
            console.warn('💡 Try: serving from localhost, or use HTTPS in production.');
        }

        return true; // Always allow initialization now, but warn
    }

    activate() {
        if (this.isActive) {
            console.log('Voice already active, skipping');
            return;
        }

        console.log(`Activating voice navigation on ${this.currentPage}`);

        this.isActive = true;

        // Add visual indicator
        this.addVisualIndicator();

        // Disable manual controls
        this.disableControls();

        // Start listening immediately and forcefully
        console.log('🎤 Starting voice recognition immediately...');
        this.forceStartListening();

        // Only speak page greeting if not already announced this session
        if (!this.pageAlreadyAnnounced) {
            console.log(`🎉 First visit to ${this.currentPage} - announcing page`);
            // Add delay before greeting to let recognition start first
            setTimeout(() => {
                this.speakPageGreeting((utterance) => {
                    // Mark page as announced after speaking greeting
                    this.markPageAsAnnounced(this.currentPage);
                    // Recognition is already running, so no need to restart
                });
            }, 1500); // Give more time for recognition to establish
        } else {
            console.log(`🔄 Returning to ${this.currentPage} - skipping announcement`);
            // Recognition is already running from above
        }
    }

    deactivate() {
        if (!this.isActive) return;

        this.isActive = false;
        if (this.isListening) {
            this.recognition.stop();
        }
        this.speak('Voice mode deactivated.');
        this.removeVisualIndicator();

        // Re-enable manual controls
        this.enableControls();
    }

    startListening() {
        // Don't start listening if we're currently speaking TTS
        if (!this.recognition || this.isListening || this.isSpeaking) return;
        try {
            this.recognition.start();
        } catch (error) {
            console.error('Error starting recognition:', error);
        }
    }

    forceStartListening() {
        // Force start recognition regardless of state - for debugging and critical initialization
        if (!this.recognition) {
            console.error('❌ No recognition instance available');
            return;
        }

        try {
            // Stop any existing recognition first
            if (this.isListening) {
                this.recognition.abort(); // Use abort to force immediate stop
                this.isListening = false;
            }

            // Clear any stuck speaking state
            this.isSpeaking = false;

            // Force start new recognition
            this.recognition.start();
            console.log('✅ Force-started voice recognition');
        } catch (error) {
            console.error('❌ Force start recognition failed:', error);

            // Try again with a delay if failed
            setTimeout(() => {
                if (this.isActive && !this.isListening) {
                    try {
                        this.recognition.start();
                        console.log('🔄 Force retry: recognition started');
                    } catch (retryError) {
                        console.error('❌ Force retry failed:', retryError);
                        // At this point, manual intervention might be needed
                        this.speak('Voice recognition failed to start. Please refresh the page or check microphone permissions.');
                    }
                }
            }, 1000);
        }
    }

    processCommand(command) {
        // Keep original command processing intact - don't change existing logic
        let matchedCommand = null;
        let bestMatchScore = 0;

        for (const [key, cmd] of Object.entries(this.commands)) {
            if (command.includes(key) || key.includes(command)) {
                const score = this.calculateMatchScore(command, key);
                if (score > bestMatchScore) {
                    bestMatchScore = score;
                    matchedCommand = cmd;
                }
            }
        }

        if (matchedCommand && bestMatchScore >= 2) {
            // Handle commands differently based on type
            if (matchedCommand.silent) {
                // Silent navigation - no TTS feedback, navigate immediately
                console.log(`🎯 Silent navigation: ${matchedCommand.response || 'navigating'}`);
                matchedCommand.action();
            } else {
                // Regular command with TTS response
                console.log(`🎯 Executing command: ${matchedCommand.response}`);
                this.speak(matchedCommand.response).then(() => {
                    if (this.isActive) {
                        matchedCommand.action();
                    }
                });
            }
            return;
        }

        // Page-specific commands (Assignments page)
        if (this.getCurrentPageName() === 'Assignments') {
            const assignmentMatch = this.parseAssignmentDetailsCommand(command);
            if (assignmentMatch) {
                // Wait for assignment response to finish, then execute action
                this.speak(assignmentMatch.response).then(() => {
                    if (this.isActive) {
                        assignmentMatch.action();
                        // Wait for action, then give follow-up
                        setTimeout(() => {
                            if (this.isActive) {
                                this.speak('What would you like to do next?').then(() => {
                                    // Start listening after follow-up finishes
                                    setTimeout(() => {
                                        if (this.isActive && !this.isListening) {
                                            this.startListening();
                                        }
                                    }, 200);
                                });
                            }
                        }, 500);
                    }
                });
                return;
            }
        }

        // Subject commands (My Courses page) - VOICE ONLY
        if (this.getCurrentPageName() === 'My Courses') {
            // Check for PDF reading commands first
            if (command.toLowerCase().includes('yes') || command.toLowerCase().includes('read')) {
                // Start PDF reading - disable recognition temporarily during reading
                this.isCurrentlyReadingPDF = true;
                this.speak('Reading PDF content...').then(() => {
                    if (this.isActive) {
                        // Simulate PDF reading (in real app, would read actual PDF content)
                        this.speak('PDF content would be read here.').then(() => {
                            if (this.isActive) {
                                this.isCurrentlyReadingPDF = false;
                                this.speak('Finished reading. What would you like to do next?');
                            }
                        });
                    }
                });
                return;
            } else if (command.toLowerCase().includes('no') || command.toLowerCase().includes('don\'t') || command.toLowerCase().includes('stop')) {
                // User doesn't want to read PDF
                this.isCurrentlyReadingPDF = false;
                this.speak('Understood. What would you like to do next?');
                return;
            }

            // Check for course opening commands
            const courseMatch = this.parseCoursesCommand(command);
            if (courseMatch) {
                // Open course immediately without clicking
                courseMatch.action(); // This should simulate clicking the course

                // Ask if user wants to read the PDF
                setTimeout(() => {
                    if (this.isActive) {
                        this.speak('Subject opened. Do you want me to read it for you? Say yes to read or no to skip.').then(() => {
                            // Keep recognition active while waiting for response
                        });
                    }
                }, 1000);
                return;
            }
        }

        // Try conversational commands...
        const understood = this.parseConversationalCommand(command);
        if (understood) {
            // Wait for conversational response to finish, then execute action
            this.speak(understood.response).then(() => {
                if (this.isActive) {
                    understood.action();
                    // Wait for action, then give follow-up
                    setTimeout(() => {
                        if (this.isActive) {
                            this.speak('Anything else?').then(() => {
                                // Start listening after follow-up finishes
                                setTimeout(() => {
                                    if (this.isActive && !this.isListening) {
                                        this.startListening();
                                    }
                                }, 200);
                            });
                        }
                    }, 500);
                }
            });
        } else {
            // No match found - wait a bit before restarting listening
            setTimeout(() => {
                if (this.isActive && !this.isListening) {
                    this.startListening();
                }
            }, 500);
        }
    }

    parseConversationalCommand(command) {
        // Handle conversational queries
        if (command.includes('where') || command.includes('how') || command.includes('check')) {
            if (command.includes('assignment')) {
                return { action: () => this.navigate('assignments.php'), response: 'You can check your assignments in the assignments section.' };
            }
            if (command.includes('exam') || command.includes('quiz') || command.includes('test')) {
                return { action: () => this.navigate('exams.php'), response: 'Your exams and quizzes are in the exams section.' };
            }
            if (command.includes('course') || command.includes('material')) {
                return { action: () => this.navigate('my_courses.php'), response: 'Find course materials in your courses section.' };
            }
            if (command.includes('profile') || command.includes('settings')) {
                return { action: () => this.navigate('profile.php'), response: 'Access your profile in the profile section.' };
            }
        }
        return null;
    }

    isNavigationCommand(command) {
        const navCommands = [
            'assignments', 'homework', 'exams', 'quizzes', 'test',
            'courses', 'my courses', 'materials', 'profile',
            'dashboard', 'home', 'settings', 'logout', 'log out', 'sign out',
            'go to assignments', 'go to my assignments', 'go to exams',
            'go to my exams', 'go to courses', 'go to my courses',
            'go to profile', 'go to my profile', 'go to settings',
            'go to dashboard', 'go to my dashboard',
            'back to dashboard', 'go back to dashboard', 'go back home', 'go home'
        ];

        return navCommands.some(navCmd => command.toLowerCase().includes(navCmd));
    }

    calculateMatchScore(command, key) {
        // Simple scoring: exact match > partial match > keyword match
        if (command === key) return 3;
        if (command.includes(key)) return 2;
        if (key.includes(command)) return 1;
        return 0;
    }

    navigate(url) {
        // Prevent multiple navigation calls
        if (this.isNavigating) return;
        this.isNavigating = true;

        // Don't announce navigation
        console.log(`🎯 Silent navigation to ${url}`);

        // Stop recognition during navigation
        if (this.isListening && this.recognition) {
            this.recognition.stop();
            this.isListening = false;
        }

        // Small delay to ensure cleanup before navigating (NO SPEECH ANNOUNCEMENT)
        setTimeout(() => {
            window.location.href = url;
        }, 100);
    }

    readDashboardStats() {
        // Extract stats from page
        const stats = [];
        document.querySelectorAll('.stat-card').forEach(card => {
            const value = card.querySelector('.stat-value').textContent;
            const label = card.querySelector('.stat-label').textContent;
            stats.push(`${value} ${label}`);
        });

        const summary = `You have ${stats.join(', and ')}.`;
        this.speak(summary);
    }

    readUpcomingDeadlines() {
        // This would need to be implemented based on page content
        // For now, generic response
        this.speak('Please navigate to the dashboard to view specific deadlines.');
    }

    readToDoList() {
        // Similar to deadlines
        this.speak('Check your dashboard for the most urgent tasks.');
    }

    listCommands() {
        const commandList = Object.keys(this.commands).filter(cmd => cmd !== 'stop' && cmd !== 'exit');
        this.speak(`Available commands include: ${commandList.slice(0, 10).join(', ')}, and more. Just say what you need.`);
    }

    speak(text, onEndCallback = null) {
        return new Promise((resolve) => {
            if (!this.synth || !this.synth.speak) {
                console.warn('Speech synthesis not supported');
                resolve();
                return;
            }

            try {
                // Cancel any ongoing speech
                this.synth.cancel();

                const utterance = new SpeechSynthesisUtterance(text);
                utterance.rate = 0.9;
                utterance.pitch = 1;
                utterance.volume = 1;

                // Try to use a natural voice (any English voice preferred)
                const voices = this.synth.getVoices() || [];

                // First preference: any English voice
                let preferredVoice = voices.find(voice =>
                    voice.lang.startsWith('en')
                );

                // If no English voice, use any available voice
                if (!preferredVoice && voices.length > 0) {
                    preferredVoice = voices[0];
                }

                if (preferredVoice) {
                    utterance.voice = preferredVoice;
                    console.log('Using voice:', preferredVoice.name);
                }

                // Stop recognition during TTS to prevent self-hearing
                if (this.isListening && this.recognition) {
                    this.recognition.stop();
                    this.isListening = false;
                }
                this.isSpeaking = true;

                // Set callbacks
                utterance.onstart = () => {
                    this.isSpeaking = true;
                    console.log('TTS started:', text.substring(0, 30) + '...');
                };

                utterance.onend = () => {
                    this.isSpeaking = false;
                    console.log('TTS completed:', text.substring(0, 30) + '...');

                    // Process any queued commands after speech finishes
                    if (this.queuedCommand && this.isActive) {
                        console.log('Processing queued command after TTS completion');
                        const commandToProcess = this.queuedCommand;
                        this.queuedCommand = null;
                        this.processCommand(commandToProcess);
                    }

                    // Call custom callback if provided
                    if (onEndCallback) {
                        onEndCallback();
                    }

                    // Restart recognition if voice navigation is active and not processing queued commands
                    if (this.isActive && !this.isListening && !this.queuedCommand) {
                        console.log('Restarting recognition after TTS completion');
                        this.startListening();
                    }

                    resolve(); // Resolve the promise
                };

                utterance.onerror = (error) => {
                    console.warn('TTS error:', error);
                    this.isSpeaking = false;
                    resolve(); // Still resolve to continue flow
                };

                this.synth.speak(utterance);

            } catch (error) {
                console.warn('Speak function error:', error);
                this.isSpeaking = false;
                resolve();
            }
        });
    }

    addVisualIndicator(status = 'Voice Mode Active') {
        // Add a visual indicator that voice mode is active
        if (document.getElementById('voice-indicator')) return;

        const indicator = document.createElement('div');
        indicator.id = 'voice-indicator';
        indicator.innerHTML = `
            <style>
                #voice-indicator {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background: linear-gradient(135deg, #3B82F6, #60A5FA);
                    color: white;
                    padding: 10px 15px;
                    border-radius: 25px;
                    box-shadow: 0 4px 12px rgba(59,130,246,0.3);
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    z-index: 1000;
                    animation: pulse 2s infinite;
                    max-width: 300px;
                    font-size: 14px;
                }
                #voice-indicator i { font-size: 16px; }
                @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
            </style>
            <i class="fas fa-microphone"></i> ${status}
        `;
        document.body.appendChild(indicator);
    }

    updateVisualIndicator(status) {
        const indicator = document.getElementById('voice-indicator');
        if (indicator) {
            // Update the text part while keeping the microphone icon
            indicator.innerHTML = `
                <style>
                    #voice-indicator {
                        position: fixed;
                        bottom: 20px;
                        right: 20px;
                        background: linear-gradient(135deg, #3B82F6, #60A5FA);
                        color: white;
                        padding: 10px 15px;
                        border-radius: 25px;
                        box-shadow: 0 4px 12px rgba(59,130,246,0.3);
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        z-index: 1000;
                        animation: pulse 2s infinite;
                        max-width: 300px;
                        font-size: 14px;
                    }
                    #voice-indicator i { font-size: 16px; }
                    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
                </style>
                <i class="fas fa-microphone"></i> ${status}
            `;
        }
    }

    removeVisualIndicator() {
        const indicator = document.getElementById('voice-indicator');
        if (indicator) {
            indicator.remove();
        }
    }

    disableControls() {
        document.querySelectorAll('#voice-mode-global-btn, #voice-mode-btn').forEach(btn => {
            if (btn) btn.disabled = true;
        });
    }

    enableControls() {
        document.querySelectorAll('#voice-mode-global-btn, #voice-mode-btn').forEach(btn => {
            if (btn) btn.disabled = false;
        });
    }

    generateSessionId() {
        return 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    getSessionId() {
        // Generate a unique session ID if not exists
        if (!sessionStorage.getItem('voiceSessionId')) {
            sessionStorage.setItem('voiceSessionId', this.generateSessionId());
        }
        return sessionStorage.getItem('voiceSessionId');
    }

    hasPageBeenAnnounced(page) {
        try {
            const announced = sessionStorage.getItem(this.announcedPagesKey);
            if (!announced) return false;
            const announcedPages = JSON.parse(announced);
            return announcedPages.includes(page);
        } catch (error) {
            console.warn('Error checking page announcement status:', error);
            return false;
        }
    }

    markPageAsAnnounced(page) {
        try {
            const announced = sessionStorage.getItem(this.announcedPagesKey) || '[]';
            const announcedPages = JSON.parse(announced);
            if (!announcedPages.includes(page)) {
                announcedPages.push(page);
                sessionStorage.setItem(this.announcedPagesKey, JSON.stringify(announcedPages));
                console.log(`✅ Marked ${page} as announced`);
            }
        } catch (error) {
            console.warn('Error marking page as announced:', error);
        }
    }

    hasIntroducedPage() {
        const introducedPages = sessionStorage.getItem('introducedPages');
        if (!introducedPages) return false;
        const pages = JSON.parse(introducedPages);
        return pages.includes(this.currentPage);
    }

    markPageIntroduced() {
        const introducedPages = sessionStorage.getItem('introducedPages') || '[]';
        const pages = JSON.parse(introducedPages);
        if (!pages.includes(this.currentPage)) {
            pages.push(this.currentPage);
            sessionStorage.setItem('introducedPages', JSON.stringify(pages));
        }
    }

    getCurrentPageName() {
        const path = window.location.pathname;
        const page = path.split('/').pop().replace('.php', '');
        switch (page) {
            case 'index': return 'Dashboard';
            case 'my_courses': return 'My Courses';
            case 'assignments': return 'Assignments';
            case 'exams': return 'Exams';
            case 'profile': return 'Profile';
            case 'settings': return 'Settings';
            default: return 'Page';
        }
    }

    readAssignments() {
        if (this.getCurrentPageName() !== 'Assignments') {
            this.speak('Please navigate to the assignments page to read your assignments.');
            return;
        }
        const cards = document.querySelectorAll('.assignment-card');
        if (!cards.length) {
            this.speak('You have no assignments yet.');
            return;
        }
        const assignments = Array.from(cards).map(card => {
            const title = card.querySelector('.title')?.textContent || 'Unknown title';
            const subject = card.querySelector('.subject')?.textContent || 'Unknown subject';
            const status = card.querySelector('.status-badge')?.textContent || 'Unknown status';
            return `${title} in ${subject}, status ${status}`;
        }).join('; ');
        this.speak(`Your assignments: ${assignments}`);
    }

    speakPageGreeting() {
        const page = this.getCurrentPageName();
        if (page === 'My Courses') {
            this.speakMyCoursesGreeting();
        } else if (page === 'Exams') {
            this.speakExamsGreeting();
        } else if (page === 'Assignments') {
            this.speakAssignmentsGreeting();
        } else {
            this.speak(`You are now on the ${page} page. What would you like to do next?`);
        }
    }

    speakMyCoursesGreeting() {
        const subjects = Array.from(document.querySelectorAll('.course-title')).map(el => el.textContent.trim());
        const subjectList = subjects.join(', ');
        this.speak(`You are now on the My Courses page. Your subjects are ${subjectList}. Which subject would you like to open?`);
    }

    speakExamsGreeting() {
        const pendingExams = Array.from(document.querySelectorAll('.exam-card')).filter(card => {
            const badge = card.querySelector('.status-badge');
            return badge && (badge.textContent === 'Not Started' || badge.textContent === 'Take Now');
        }).map(card => {
            const title = card.querySelector('.card-title')?.textContent.trim() || 'Unknown exam';
            const course = card.querySelector('.card-course')?.textContent.split(' • ')[0].trim() || 'Unknown subject';
            const status = card.querySelector('.status-badge')?.textContent || 'Unknown';
            return `${title} in ${course}, status ${status}`;
        });
        let greeting = 'You are now in the Exams page. ';
        if (pendingExams.length > 0) {
            const list = pendingExams.join('; ');
            greeting += `You have the following exams available: ${list}. Which subject exam do you want to answer today?`;
        } else {
            greeting += 'You have no pending exams at this time.';
        }
        greeting += ' What would you like to do next?';
        this.speak(greeting);
    }

    speakPendingExams() {
        const pendingExams = Array.from(document.querySelectorAll('.exam-card')).filter(card => {
            const badge = card.querySelector('.status-badge');
            return badge && (badge.textContent === 'Not Started' || badge.textContent === 'Take Now');
        }).map(card => {
            const title = card.querySelector('.card-title')?.textContent.trim() || 'Unknown exam';
            const course = card.querySelector('.card-course')?.textContent.split(' • ')[0].trim() || 'Unknown subject';
            const status = card.querySelector('.status-badge')?.textContent || 'Unknown';
            return `${title} in ${course}, status ${status}`;
        });
        if (pendingExams.length > 0) {
            const list = pendingExams.join('; ');
            this.speak(`Pending exams: ${list}. What would you like to do next?`);
        } else {
            this.speak('You have no pending exams at this time. What would you like to do next?');
        }
    }

    speakMissedExams() {
        const missedExams = Array.from(document.querySelectorAll('.exam-card')).filter(card => {
            const badge = card.querySelector('.status-badge');
            return badge && badge.textContent === 'Expired';
        }).map(card => {
            const title = card.querySelector('.card-title')?.textContent.trim() || 'Unknown exam';
            const course = card.querySelector('.card-course')?.textContent.split(' • ')[0].trim() || 'Unknown subject';
            return `${title} in ${course}`;
        });
        if (missedExams.length > 0) {
            const list = missedExams.join('; ');
            this.speak(`Missed exams: ${list}. What would you like to do next?`);
        } else {
            this.speak('You have no missed exams. What would you like to do next?');
        }
    }

    speakAssignmentsGreeting() {
        const pendingAssignments = Array.from(document.querySelectorAll('.assignment-card')).filter(card => {
            const status = card.dataset.status;
            return status === 'pending';
        }).map(card => {
            return card.querySelector('.subject')?.textContent.trim() || 'Unknown subject';
        });

        // Get unique subjects with pending assignments
        const uniqueSubjects = [...new Set(pendingAssignments)];

        let greeting = 'You are now on the Assignments page. ';

        if (uniqueSubjects.length > 0) {
            const subjectList = uniqueSubjects.join(', ');
            greeting += `You have pending assignments in ${subjectList}.`;
        } else {
            greeting += 'You currently have no pending assignments.';
        }

        greeting += ' What would you like to do next?';
        this.speak(greeting);
    }

    openPendingAssignments() {
        if (this.getCurrentPageName() !== 'Assignments') {
            this.navigate('assignments.php');
            return;
        }
        const filter = document.getElementById('filterStatus');
        if (filter) {
            filter.value = 'pending';
            filter.dispatchEvent(new Event('change'));
        }
    }

    parseAssignmentDetailsCommand(command) {
        // Match patterns like "read the details for Math assignment" or "read details for Science assignment"
        const patterns = [
            /read\s+(the\s+)?details?\s+for\s+(\w+)\s+assignment/i,
            /read\s+(\w+)\s+assignment\s+details?/i,
            /tell\s+me\s+about\s+(\w+)\s+assignment/i
        ];

        for (const pattern of patterns) {
            const match = command.match(pattern);
            if (match) {
                const subject = match[2] || match[1];
                const assignmentCard = this.findAssignmentBySubject(subject);
                if (assignmentCard) {
                    return {
                        action: () => this.readAssignmentDetails(assignmentCard),
                        response: `Reading details for ${subject} assignment.`
                    };
                } else {
                    return {
                        action: () => {},
                        response: `I couldn't find an assignment for ${subject}.`
                    };
                }
            }
        }
        return null;
    }

    findAssignmentBySubject(subject) {
        // Find the first visible assignment card that includes the subject name
        const cards = document.querySelectorAll('.assignment-card');
        const subjectLower = subject.toLowerCase();
        for (const card of cards) {
            if (card.style.display !== 'none') { // Only check visible cards
                const cardSubject = card.querySelector('.subject')?.textContent.trim().toLowerCase();
                if (cardSubject && cardSubject.includes(subjectLower)) {
                    return card;
                }
            }
        }
        return null;
    }

    readAssignmentDetails(card) {
        const title = card.querySelector('.title')?.textContent.trim() || 'Unknown title';
        const subject = card.querySelector('.subject')?.textContent.trim() || 'Unknown subject';
        const dueDate = card.querySelector('.due')?.textContent.trim() || 'Unknown due date';
        const status = card.querySelector('.status-badge')?.textContent.trim() || 'Unknown status';
        const description = card.querySelector('.desc')?.textContent.trim() || 'No description available';

        const details = `${title} in ${subject}. Due ${dueDate}. Status: ${status}. ${description}`;
        this.speak(details);
    }

    parseCoursesCommand(command) {
        // Match "open [subject]" patterns
        const patterns = [
            /^open\s+(\w+)/i,
            /^go\s+to\s+(\w+)/i,
            /^study\s+(\w+)/i,
            /^learn\s+(\w+)/i
        ];

        for (const pattern of patterns) {
            const match = command.match(pattern);
            if (match) {
                const subject = match[1];
                const courseCard = this.findCourseBySubject(subject);
                if (courseCard) {
                    const actualSubject = courseCard.querySelector('.course-title')?.textContent.trim() || subject;
                    return {
                        action: () => this.openCourseMaterial(courseCard),
                        response: `Opening ${actualSubject} learning materials.`
                    };
                } else {
                    return {
                        action: () => {},
                        response: `I couldn't find a course for ${subject}.`
                    };
                }
            }
        }
        return null;
    }

    findCourseBySubject(subject) {
        // Find course card by subject name (case insensitive partial match)
        const cards = document.querySelectorAll('.course-card');
        const subjectLower = subject.toLowerCase();
        for (const card of cards) {
            const cardSubject = card.querySelector('.course-title')?.textContent.trim().toLowerCase();
            if (cardSubject && cardSubject.includes(subjectLower)) {
                return card;
            }
        }
        return null;
    }

    openCourseMaterial(courseCard) {
        // Find the first "Open →" button in the course card and click it
        const openBtn = courseCard.querySelector('.open-btn');
        if (openBtn) {
            openBtn.click();
        } else {
            this.speak('No materials available for this course.');
        }
    }
}

// Initialize when DOM loads - FULLY VOICE DRIVEN, NO CLICKS NEEDED
document.addEventListener('DOMContentLoaded', function() {
    // Prevent multiple initializations of Voice Navigation
    if (window.voiceNavInstanceInititalized) {
        console.log('Voice navigation already initialized, skipping');
        return;
    }
    window.voiceNavInstanceInititalized = true;

    // Prevent multiple VoiceNavigation instances
    if (window.voiceNav) {
        console.log('VoiceNavigation instance already exists, reusing');
        return;
    }

    window.voiceNav = new VoiceNavigation();

    // NO CLICK BUTTONS - Everything is voice controlled
    console.log('🎯 Voice navigation initialized - fully voice controlled');
});
