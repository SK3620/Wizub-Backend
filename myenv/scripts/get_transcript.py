# get_transcript.py
import sys
import json
from youtube_transcript_api import YouTubeTranscriptApi

def get_transcript(video_id):
    transcript_list = YouTubeTranscriptApi.list_transcripts(video_id)

    # 英語の字幕を取得し保存
    transcript = transcript_list.find_transcript(['en'])
    transcript_data = transcript.fetch()

    transcripts = []
    for tr in transcript_data:
            transcripts.append({
                'text': tr['text'],
                'start': tr['start'],
                'duration': tr['duration']
            })

    return {'transcripts': transcripts}

if __name__ == "__main__":
    video_id = sys.argv[1]
    transcripts = get_transcript(video_id)
    print(json.dumps(transcripts, ensure_ascii=False))