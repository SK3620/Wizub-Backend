import openai
import sys
import json

# 引数を取得
api_key = sys.argv[1]
content = sys.argv[2]

# APIキーを設定
openai.api_key = api_key

def get_openai_response(content):
    response = openai.ChatCompletion.create(
        model="gpt-3.5-turbo-0125",
        messages=[
            {
                "role": "system",
                "content": (
                    "英文を自然な日本語に訳して、JSON形式で返答してください。"
                    "フォーマットは以下にしてください。\n"
                    '''"response": {"（ID）": "（日本語訳）, （ID）": "（日本語訳）", ... }'''
                ),
            },
            {
                "role": "user",
                "content": content,
            },
        ],
    )
    return response.choices[0].message['content']

if __name__ == "__main__":
    content = sys.argv[2]
    response = get_openai_response(content)
    print(response)
