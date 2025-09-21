<!-- Audio during the shuffling -->
<div class="audio-setting p-2 mb-1">
    <div class="form-check border-bottom mb-3">
        <input type="checkbox" class="form-check-input toggle-audio-settings" name="setting-toggle[0]" id="toggle-audio-settings-0" data-media-type="<?= AUDIO_TYPE_BRACKET_GENERATION ?>" onChange="audioSettingToggleChange(this)">
        <label class="form-check-label" for="toggle-audio-settings-0">
            <h6>Audio during Brackets Generation</h6>
        </label>
    </div>

    <div class="setting visually-hidden">
        <input type="hidden" name="audioType[0]" value="0">
        <div class="input-group mb-3">
            <div class="input-group-text">
                <input class="form-check-input mt-0" type="radio" value="f" aria-label="Radio button for following text input" name="source[0]" onChange="audioSourceChange(this)" data-target="file" checked>
            </div>
            <input type="file" class="form-control audio-source" data-source="file" name="file" onChange="audioFileUpload(this)" accept="audio/mpeg,audio/wav,audio/ogg,audio/mid,audio/x-midi" required disabled>
            <input type="hidden" class="file-path" id="file-shuffling-audio" name="file-path[0]">
            <div class="fileupload-hint form-text">Select a .mp3 file to upload(max file size is 100MB). After waiting a bit, you will notice the player loads the file here when the timestamps appear, in which you could then adjust accordingly.</div>
        </div>
        <div class="input-group mb-3">
            <div class="input-group-text">
                <input class="audio-url-enable form-check-input mt-0" type="radio" value="y" aria-label="Radio button for following text input" name="source[0]" onChange="audioSourceChange(this)" data-target="url">
            </div>
            <span class="input-group-text">URL</span>
            <input type="text" class="form-control audio-source" data-source="url" aria-describedby="basic-addon3 basic-addon4" name="url[0]" placeholder="Example: https://youtu.be/Gb1iGDchKYs?si=nR-n7KBMHfKWox7U" required disabled>
            <div class="invalid-feedback">This field is required.</div>
            <div class="urlupload-hint form-text d-none">Enter a YouTube video url. <br />Note an <a href="https://developers.google.com/youtube/v3/guides/implementation/videos">API</a> will process the video into a .mp3 file in the backend once you click save. You may specify the timestamps before saving or you may revise the timestamps by navigating to the tournament's audio settings action after it's generated.</div>
        </div>
        <div class="mb-3 preview">
            <audio controls class="w-100 player">
                <source class="playerSource" src="" type="audio/mpeg" />
            </audio>

            <div class="row g-3">
                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-text">Start</div>
                        <input type="text" class="form-control time startAt" onChange="audioDurationChange(this)" placeholder="00:00:00" pattern="^([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])$" required disabled>
                        <input type="hidden" class="form-control form-control-sm startAt" name="start[0]">
                    </div>
                    <div class="invalid-feedback d-none" id="start-time-error-0">Start time must be less than stop time.</div>
                </div>

                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-text">Stop</div>
                        <input type="text" class="form-control time stopAt" onChange="audioDurationChange(this)" placeholder="00:00:00" pattern="^([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])$" required disabled>
                        <input type="hidden" class="form-control form-control-sm stopAt" name="stop[0]">
                    </div>
                    <div class="invalid-feedback d-none" id="stop-time-error-0">Stop time must be greater than start time.</div>
                </div>

                <input type="hidden" class="duration" name="duration[0]" value="5">
            </div>
        </div>
    </div>
</div>

<!-- Audio for the Final Winner -->
<div class="audio-setting p-2 mb-1">
    <div class="form-check border-bottom mb-3">
        <input type="checkbox" class="form-check-input toggle-audio-settings" name="setting-toggle[1]" id="toggle-audio-settings-1" data-media-type="<?= AUDIO_TYPE_FINAL_WINNER ?>" onChange="audioSettingToggleChange(this)">
        <label class="form-check-label" for="toggle-audio-settings-1">
            <h6>Audio for a Final Winner</h6>
        </label>
    </div>

    <div class="setting visually-hidden">
        <div class="row ps-3">
            <div class="col-md-12 col-sm-12 form-check ps-2">
                <input type="checkbox" id="winnerAudioForEveryone" class="form-check-input ms-0" name="winner_audio_everyone">
                <label for="winnerAudioForEveryone" class="form-check-label ms-1">Play for everyone</label>
            </div>
            <div class="col-md-12 pl-2 winnerAudioForEveryone-hint form-text">
                <p><?= lang('Descriptions.tournamentPlayForEveryoneDesc') ?></p>
            </div>
        </div>
        <input type="hidden" name="audioType[1]" value="1">
        <div class="input-group mb-3">
            <div class="input-group-text">
                <input class="form-check-input mt-0" type="radio" value="f" aria-label="Radio button for following text input" name="source[1]" onChange="audioSourceChange(this)" data-target="file" checked>
            </div>
            <input type="file" class="form-control audio-source" data-source="file" name="file" onChange="audioFileUpload(this)" accept="audio/mpeg,audio/wav,audio/ogg,audio/mid,audio/x-midi" required disabled>
            <input type="hidden" class="file-path" id="file-input" name="file-path[1]">
            <div class="invalid-feedback">This field is required.</div>
            <div class="fileupload-hint form-text">Select a .mp3 file to upload(max file size is 100MB). After waiting a bit, you will notice the player loads the file here when the timestamps appear, in which you could then adjust accordingly.</div>
        </div>
        <div class="input-group mb-3">
            <div class="input-group-text">
                <input class="audio-url-enable form-check-input mt-0" type="radio" value="y" aria-label="Radio button for following text input" name="source[1]" onChange="audioSourceChange(this)" data-target="url">
            </div>
            <span class="input-group-text">URL</span>
            <input type="text" class="form-control audio-source" data-source="url" aria-describedby="basic-addon3 basic-addon4" name="url[1]" placeholder="Example: https://youtu.be/Gb1iGDchKYs?si=nR-n7KBMHfKWox7U" required disabled>
            <div class="invalid-feedback">This field is required.</div>
            <div class="urlupload-hint form-text d-none">Enter a YouTube video url. <br />Note an <a href="https://developers.google.com/youtube/v3/guides/implementation/videos">API</a> will process the video into a .mp3 file in the backend once you click save. You may specify the timestamps before saving or you may revise the timestamps by navigating to the tournament's audio settings action after it's generated.</div>
        </div>
        <div class="mb-3 preview">
            <audio controls class="w-100 player">
                <source class="playerSource" src="" type="audio/mpeg" />
            </audio>

            <div class="row g-3">
                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-text">Start</div>
                        <input type="text" class="form-control time startAt" onChange="audioDurationChange(this)" placeholder="00:00:00" pattern="^([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])$" required disabled>
                        <input type="hidden" class="form-control form-control-sm startAt" name="start[1]">
                    </div>
                    <div class="invalid-feedback d-none" id="start-time-error-1">Start time must be less than stop time.</div>
                </div>

                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-text">Stop</div>
                        <input type="text" class="form-control time stopAt" onChange="audioDurationChange(this)" placeholder="00:00:00" pattern="^([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])$" required disabled>
                        <input type="hidden" class="form-control form-control-sm stopAt" name="stop[1]">
                    </div>
                    <div class="invalid-feedback d-none" id="stop-time-error-1">Stop time must be greater than start time.</div>
                </div>

                <input type="hidden" class="duration" name="duration[1]" value="5">
            </div>
        </div>
    </div>
</div>

<!-- Video during the shuffling -->
<div class="audio-setting p-2 mb-1">
    <div class="form-check border-bottom mb-3">
        <input type="checkbox" class="form-check-input toggle-audio-settings" name="setting-toggle[<?= AUDIO_TYPE_BRACKET_GENERATION_VIDEO ?>]" data-media-type="<?= AUDIO_TYPE_BRACKET_GENERATION_VIDEO ?>" id="toggle-audio-settings-2" onChange="audioSettingToggleChange(this)">
        <label class="form-check-label" for="toggle-audio-settings-2">
            <h6>Video during Brackets Generation</h6>
        </label>
    </div>

    <div class="setting visually-hidden">
        <input type="hidden" name="audioType[<?= AUDIO_TYPE_BRACKET_GENERATION_VIDEO ?>]" value="<?= AUDIO_TYPE_BRACKET_GENERATION_VIDEO ?>">
        <div class="input-group mb-3">
            <div class="input-group-text">
                <input class="form-check-input mt-0" type="radio" value="f" aria-label="Radio button for following text input" name="source[<?= AUDIO_TYPE_BRACKET_GENERATION_VIDEO ?>]" onChange="audioSourceChange(this)" data-target="file" checked>
            </div>
            <input type="file" class="form-control audio-source" data-source="file" name="file" onChange="videoFileUpload(this)" accept="video/mp4" required disabled>
            <input type="hidden" class="file-path" id="file-shuffling-video" name="file-path[<?= AUDIO_TYPE_BRACKET_GENERATION_VIDEO ?>]">
            <div class="fileupload-hint form-text">Select a *.mp4 file to upload (max file size is 500MB). After waiting a bit, you will notice the player loads the file here when the timestamps appear, in which you could then adjust accordingly.</div>
        </div>
        <div class="input-group mb-3">
            <div class="input-group-text">
                <input class="audio-url-enable form-check-input mt-0" type="radio" value="y" aria-label="Radio button for following text input" name="source[<?= AUDIO_TYPE_BRACKET_GENERATION_VIDEO ?>]" onChange="audioSourceChange(this)" data-target="url">
            </div>
            <span class="input-group-text">URL</span>
            <input type="text" class="form-control audio-source" data-source="url" aria-describedby="basic-addon3 basic-addon4" name="url[<?= AUDIO_TYPE_BRACKET_GENERATION_VIDEO ?>]" placeholder="Example: https://youtu.be/Gb1iGDchKYs?si=nR-n7KBMHfKWox7U" required disabled>
            <div class="invalid-feedback">This field is required.</div>
            <div class="urlupload-hint form-text d-none">Enter a YouTube video url. <br />Note an <a href="https://developers.google.com/youtube/v3/guides/implementation/videos">API</a> will process the video into a .mp4 file in the backend once you click save. You may specify the timestamps before saving or you may revise the timestamps by navigating to the tournament's audio settings action after it's generated.</div>
        </div>
        <div class="mb-3 preview">
            <video controls class="w-100 player d-none">
                <source class="playerSource" src="" type="video/mp4" />
            </video>

            <div class="row g-3">
                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-text">Start</div>
                        <input type="text" class="form-control time startAt" onChange="audioDurationChange(this)" placeholder="00:00:00" pattern="^([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])$" required disabled>
                        <input type="hidden" class="form-control form-control-sm startAt" name="start[<?= AUDIO_TYPE_BRACKET_GENERATION_VIDEO ?>]">
                    </div>
                    <div class="invalid-feedback d-none" id="start-time-error-<?= AUDIO_TYPE_BRACKET_GENERATION_VIDEO ?>">Start time must be less than stop time.</div>
                </div>

                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-text">Stop</div>
                        <input type="text" class="form-control time stopAt" onChange="audioDurationChange(this)" placeholder="00:00:00" pattern="^([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])$" required disabled>
                        <input type="hidden" class="form-control form-control-sm stopAt" name="stop[<?= AUDIO_TYPE_BRACKET_GENERATION_VIDEO ?>]">
                    </div>
                    <div class="invalid-feedback d-none" id="stop-time-error-<?= AUDIO_TYPE_BRACKET_GENERATION_VIDEO ?>">Stop time must be greater than start time.</div>
                </div>

                <input type="hidden" class="duration" name="duration[<?= AUDIO_TYPE_BRACKET_GENERATION_VIDEO ?>]" value="5">
            </div>
        </div>
    </div>
</div>