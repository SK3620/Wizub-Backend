import sys
import json
from youtube_transcript_api import YouTubeTranscriptApi

# 引数を取得
video_id = sys.argv[1]
proxy = sys.argv[2]

def get_transcript(video_id, proxy):

    # プロキシサーバーを仲介する
    transcript_list = YouTubeTranscriptApi.list_transcripts(video_id, proxies = {
        'http': proxy,
        'https': proxy
    })

    # 英語の字幕を取得し保存
    transcript = transcript_list.find_transcript(['en'])
    transcript_data = transcript.fetch()

    # IDのカウンターを初期化
    id_counter = 1

    transcripts = []
    for i in range(0, len(transcript_data), 2):
        # 1つ目の要素を取得
        text1 = transcript_data[i]['text']
        start = transcript_data[i]['start']
        duration = transcript_data[i]['duration']

        # 2つ目の要素が存在するかを確認し、存在すれば取得
        if i + 1 < len(transcript_data):
            text2 = transcript_data[i + 1]['text']
            duration += transcript_data[i + 1]['duration']  # durationを加算
        else:
            text2 = ''

        # text1とtext2を結合して'text'に入れる
        combined_text = text1 + " " + text2

        # レスポンスのフォーマット調整
        transcripts.append({
            'id': id_counter, # 暫定で一意性を保証しておく DBへの保存時、プライマリーキーとして自動インクリメント
            'subtitle_id': id_counter, # それぞれの動画のトランスクリプトのID
            'en_subtitle': combined_text, # 英語字幕
            'ja_subtitle': '', # 日本語字幕 日本語字幕は取得しないため空
            'memo': '' , # 学習メモ
            'start': start, # 字幕表示開始時間
            'duration': duration # 字幕表示時間
        })

        # IDカウンターをインクリメント
        id_counter += 1

    return {'subtitles': transcripts}

if __name__ == "__main__":
    video_id = sys.argv[1]
    proxy = sys.argv[2]
    transcripts = get_transcript(video_id, proxy)
    print(json.dumps(transcripts, ensure_ascii=False))
